<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\FixedAsset;
use App\Services\FixedAssetService;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    public function __construct(private FixedAssetService $fixedAssetService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('accounting.fixed-assets.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounting.fixed-assets.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fixed_asset_category_id' => 'required|exists:fixed_asset_categories,id',
            'chart_of_account_id' => 'nullable|exists:chart_of_accounts,id',
            'accumulated_depreciation_account_id' => 'nullable|exists:chart_of_accounts,id',
            'asset_tag' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|string|max:255',
            'purchase_date' => 'required|date|before_or_equal:today',
            'purchase_cost' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1|max:50',
            'depreciation_method' => 'required|in:straight_line,declining_balance,sum_of_years',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $asset = $this->fixedAssetService->registerAsset($validated);

            return response()->json([
                'success' => true,
                'message' => 'Asset registered successfully.',
                'asset' => $asset,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error registering asset: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FixedAsset $asset)
    {
        $asset->load(['category', 'assetAccount', 'accumulatedDepreciationAccount', 'depreciations']);

        return view('accounting.fixed-assets.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FixedAsset $asset)
    {
        return view('accounting.fixed-assets.edit', compact('asset'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FixedAsset $asset)
    {
        $validated = $request->validate([
            'fixed_asset_category_id' => 'required|exists:fixed_asset_categories,id',
            'chart_of_account_id' => 'nullable|exists:chart_of_accounts,id',
            'accumulated_depreciation_account_id' => 'nullable|exists:chart_of_accounts,id',
            'asset_tag' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|string|max:255',
            'purchase_date' => 'required|date|before_or_equal:today',
            'purchase_cost' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1|max:50',
            'depreciation_method' => 'required|in:straight_line,declining_balance,sum_of_years',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $validated['updated_by'] = auth()->id();
            $asset->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Asset updated successfully.',
                'asset' => $asset,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating asset: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FixedAsset $asset)
    {
        try {
            $asset->delete();

            return response()->json([
                'success' => true,
                'message' => 'Asset deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting asset: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Post depreciation for assets
     */
    public function postDepreciation(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'asset_ids' => 'nullable|array',
            'asset_ids.*' => 'exists:fixed_assets,id',
        ]);

        try {
            $date = new \DateTime($validated['date']);
            $result = $this->fixedAssetService->bulkDepreciation($date, auth()->user()->current_organization_id);

            return response()->json([
                'success' => true,
                'message' => "Depreciation processed: {$result['processed']} assets, {$result['errors']} errors.",
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error posting depreciation: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download asset register PDF
     */
    public function downloadAssetRegister(Request $request)
    {
        $organizationId = auth()->user()->current_organization_id;

        $assets = FixedAsset::query()
            ->where('organization_id', $organizationId)
            ->with(['category', 'assetAccount'])
            ->when($request->category, function ($query) use ($request) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('code', $request->category);
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('asset_tag')
            ->get();

        $pdfService = app(\App\Services\AccountingPdfService::class);
        $pdf = $pdfService->generateAssetRegister($assets);

        return $pdf->download('asset-register.pdf');
    }

    /**
     * Download depreciation schedule PDF
     */
    public function downloadDepreciationSchedule(Request $request)
    {
        $organizationId = auth()->user()->current_organization_id;

        $assets = FixedAsset::query()
            ->where('organization_id', $organizationId)
            ->with(['category', 'depreciations'])
            ->active()
            ->orderBy('asset_tag')
            ->get();

        $pdfService = app(\App\Services\AccountingPdfService::class);
        $pdf = $pdfService->generateDepreciationSchedule($assets);

        return $pdf->download('depreciation-schedule.pdf');
    }
}
