<?php

namespace App\Services;

use App\Models\Accounting\AssetDisposal;
use App\Models\Accounting\AssetMaintenance;
use App\Models\Accounting\AssetTransfer;
use App\Models\Accounting\Depreciation;
use App\Models\Accounting\FixedAsset;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixedAssetService
{
    public function __construct(
        private AccountingService $accountingService,
        private SequenceService $sequenceService
    ) {}

    public function registerAsset(array $data): FixedAsset
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            $data['current_book_value'] = $data['purchase_cost'];
            $data['accumulated_depreciation'] = 0;

            $asset = FixedAsset::create($data);

            // Load relationships to avoid lazy loading issues
            $asset->load(['assetAccount', 'accumulatedDepreciationAccount']);

            // Create acquisition journal entry if accounts are specified
            if (isset($data['chart_of_account_id']) && $data['created_by']) {
                $this->createAcquisitionEntry($asset, $data['created_by']);
            }

            return $asset;
        });
    }

    public function calculateDepreciation(FixedAsset $asset, ?\DateTime $date = null): ?float
    {
        if (! $asset->canBeDepreciated()) {
            return null;
        }

        $date = $date ?? now();

        // Check if depreciation should be calculated (monthly, annually)
        if (! $this->shouldCalculateDepreciation($asset, $date)) {
            return null;
        }

        return $asset->calculateAnnualDepreciation();
    }

    public function postDepreciation(FixedAsset $asset, ?\DateTime $date = null): Depreciation
    {
        return DB::transaction(function () use ($asset, $date) {
            $depreciationAmount = $this->calculateDepreciation($asset, $date);

            if ($depreciationAmount === null || $depreciationAmount <= 0) {
                throw new \InvalidArgumentException('No depreciation can be calculated for this asset');
            }

            $date = $date ?? now();

            $accumulatedBefore = $asset->accumulated_depreciation;
            $bookValueBefore = $asset->current_book_value;
            $accumulatedAfter = $accumulatedBefore + $depreciationAmount;
            $bookValueAfter = $bookValueBefore - $depreciationAmount;

            // Create depreciation record
            $depreciation = Depreciation::create([
                'organization_id' => $asset->organization_id,
                'fixed_asset_id' => $asset->id,
                'depreciation_date' => $date,
                'depreciation_amount' => $depreciationAmount,
                'accumulated_depreciation_before' => $accumulatedBefore,
                'accumulated_depreciation_after' => $accumulatedAfter,
                'book_value_before' => $bookValueBefore,
                'book_value_after' => $bookValueAfter,
                'depreciation_method' => $asset->depreciation_method,
                'notes' => "Automatic depreciation posting for {$date->format('F Y')}",
                'created_by' => Auth::id(),
            ]);

            // Create journal entry
            $journalEntry = $this->createDepreciationJournalEntry($asset, $depreciationAmount, $date);
            $depreciation->journal_entry_id = $journalEntry->id;
            $depreciation->save();

            // Update asset
            $asset->update([
                'accumulated_depreciation' => $accumulatedAfter,
                'current_book_value' => $bookValueAfter,
                'last_depreciation_date' => $date,
            ]);

            return $depreciation;
        });
    }

    public function transferAsset(FixedAsset $asset, array $transferData): AssetTransfer
    {
        return DB::transaction(function () use ($asset, $transferData) {
            $transferData['organization_id'] = $asset->organization_id;
            $transferData['fixed_asset_id'] = $asset->id;
            $transferData['from_location'] = $asset->location;
            $transferData['from_department'] = $asset->department;
            $transferData['from_assigned_to'] = $asset->assigned_to;
            $transferData['created_by'] = Auth::id();

            return AssetTransfer::create($transferData);
        });
    }

    public function disposeAsset(FixedAsset $asset, array $disposalData): AssetDisposal
    {
        return DB::transaction(function () use ($asset, $disposalData) {
            $disposalData['organization_id'] = $asset->organization_id;
            $disposalData['fixed_asset_id'] = $asset->id;
            $disposalData['book_value_at_disposal'] = $asset->current_book_value;
            $disposalData['accumulated_depreciation_at_disposal'] = $asset->accumulated_depreciation;
            $disposalData['created_by'] = Auth::id();

            $disposal = AssetDisposal::create($disposalData);

            // Create disposal journal entry
            if (isset($disposalData['chart_of_account_id'])) {
                $journalEntry = $this->createDisposalJournalEntry($asset, $disposal);
                $disposal->journal_entry_id = $journalEntry->id;
                $disposal->save();
            }

            // Update asset status
            $asset->update(['status' => 'disposed']);

            return $disposal;
        });
    }

    public function recordMaintenance(FixedAsset $asset, array $maintenanceData): AssetMaintenance
    {
        $maintenanceData['organization_id'] = $asset->organization_id;
        $maintenanceData['fixed_asset_id'] = $asset->id;
        $maintenanceData['created_by'] = Auth::id();

        return AssetMaintenance::create($maintenanceData);
    }

    public function bulkDepreciation(\DateTime $date, ?int $organizationId = null): array
    {
        $assets = FixedAsset::query()
            ->active()
            ->when($organizationId, fn ($query) => $query->where('organization_id', $organizationId))
            ->where('status', 'active')
            ->where(fn ($query) => $query
                ->whereNull('last_depreciation_date')
                ->orWhere('last_depreciation_date', '<', $date->format('Y-m-d'))
            )
            ->get();

        $results = [];
        $errors = [];

        foreach ($assets as $asset) {
            try {
                $depreciation = $this->postDepreciation($asset, $date);
                $results[] = $depreciation;
            } catch (\Exception $e) {
                $errors[] = [
                    'asset' => $asset->asset_tag,
                    'error' => $e->getMessage(),
                ];
                Log::error("Depreciation failed for asset {$asset->asset_tag}: ".$e->getMessage());
            }
        }

        return [
            'processed' => count($results),
            'errors' => count($errors),
            'results' => $results,
            'error_details' => $errors,
        ];
    }

    private function shouldCalculateDepreciation(FixedAsset $asset, \DateTime $date): bool
    {
        // If never depreciated, check if it's time for first depreciation
        if (! $asset->last_depreciation_date) {
            $purchaseDate = $asset->purchase_date;
            $monthsDiff = $purchaseDate->diffInMonths($date);

            return $monthsDiff >= 1; // Depreciate after 1 month
        }

        // Check if at least one month has passed since last depreciation
        $monthsDiff = $asset->last_depreciation_date->diffInMonths($date);

        return $monthsDiff >= 1;
    }

    private function createAcquisitionEntry(FixedAsset $asset, int $createdBy): JournalEntry
    {
        $description = "Acquisition of fixed asset: {$asset->name} ({$asset->asset_tag})";

        // Get cash/bank account for credit
        $cashAccount = ChartOfAccount::where('type', 'asset')
            ->where('name', 'like', '%cash%')
            ->orWhere('name', 'like', '%bank%')
            ->where('organization_id', $asset->organization_id)
            ->first();

        if (! $cashAccount) {
            throw new \Exception('No cash or bank account found for asset acquisition');
        }

        $entries = [
            [
                'account' => $asset->assetAccount,
                'type' => 'debit',
                'amount' => $asset->purchase_cost,
            ],
            [
                'account' => $cashAccount,
                'type' => 'credit',
                'amount' => $asset->purchase_cost,
            ],
        ];

        $this->accountingService->postTransaction(
            $entries,
            $description,
            $asset
        );

        // Create journal entry record for tracking
        return JournalEntry::create([
            'organization_id' => $asset->organization_id,
            'voucher_type' => 'asset_acquisition',
            'entry_date' => $asset->purchase_date,
            'description' => $description,
            'total_amount' => $asset->purchase_cost,
            'status' => 'posted',
            'created_by' => $createdBy,
        ]);
    }

    private function createDepreciationJournalEntry(FixedAsset $asset, float $amount, \DateTime $date, int $createdBy): JournalEntry
    {
        $description = "Depreciation for {$asset->name} ({$asset->asset_tag}) - {$date->format('F Y')}";

        $entries = [
            [
                'account' => ChartOfAccount::where('type', 'expense')->first(), // Depreciation expense
                'type' => 'debit',
                'amount' => $amount,
            ],
            [
                'account' => $asset->accumulatedDepreciationAccount,
                'type' => 'credit',
                'amount' => $amount,
            ],
        ];

        $this->accountingService->postTransaction(
            $entries,
            $description,
            $asset
        );

        // Create journal entry record for tracking
        return JournalEntry::create([
            'organization_id' => $asset->organization_id,
            'voucher_type' => 'depreciation',
            'entry_date' => $date,
            'description' => $description,
            'total_amount' => $amount,
            'status' => 'posted',
            'created_by' => $createdBy,
        ]);
    }

    private function createDisposalJournalEntry(FixedAsset $asset, AssetDisposal $disposal): JournalEntry
    {
        $description = "Disposal of fixed asset: {$asset->name} ({$asset->asset_tag})";

        $entries = [
            // Remove accumulated depreciation
            [
                'account' => $asset->accumulatedDepreciationAccount,
                'type' => 'debit',
                'amount' => $disposal->accumulated_depreciation_at_disposal,
            ],
        ];

        // Add proceeds if any
        if ($disposal->proceeds > 0) {
            $entries[] = [
                'account' => ChartOfAccount::where('type', 'asset')->first(), // Cash/bank account
                'type' => 'debit',
                'amount' => $disposal->proceeds,
            ];
        }

        // Credit the asset account
        $entries[] = [
            'account' => $asset->assetAccount,
            'type' => 'credit',
            'amount' => $asset->purchase_cost,
        ];

        // Handle gain/loss
        if ($disposal->gain_loss != 0) {
            $gainLossAccount = $disposal->gain_loss > 0
                ? ChartOfAccount::where('type', 'revenue')->first() // Gain on disposal
                : ChartOfAccount::where('type', 'expense')->first(); // Loss on disposal

            $entries[] = [
                'account' => $gainLossAccount,
                'type' => $disposal->gain_loss > 0 ? 'credit' : 'debit',
                'amount' => abs($disposal->gain_loss),
            ];
        }

        return $this->accountingService->createJournalEntry(
            $entries,
            $description,
            $asset->organization_id,
            [
                'voucher_type' => 'asset_disposal',
                'entry_date' => $disposal->disposal_date,
            ]
        );
    }
}
