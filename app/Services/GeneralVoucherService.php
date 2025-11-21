<?php

namespace App\Services;

use App\Models\Accounting\Voucher;
use Exception;

class GeneralVoucherService
{
    /**
     * Create a new voucher.
     */
    public function createVoucher(array $data, int $organizationId, int $userId): Voucher
    {
        $this->validateVoucherData($data);

        $voucherData = array_merge($data, [
            'organization_id' => $organizationId,
            'number' => Voucher::generateNumber($data['type'], $organizationId),
            'status' => 'draft',
            'created_by' => $userId,
        ]);

        return Voucher::create($voucherData);
    }

    /**
     * Post (finalize) a voucher.
     */
    public function postVoucher(int $voucherId, int $userId): Voucher
    {
        $voucher = Voucher::findOrFail($voucherId);

        if ($voucher->isPosted()) {
            throw new Exception('Voucher is already posted');
        }

        $voucher->update([
            'status' => 'posted',
            'updated_by' => $userId,
        ]);

        // TODO: Create journal entries for double-entry accounting
        // This will be implemented when we integrate with the journal system

        return $voucher->fresh();
    }

    /**
     * Update an existing voucher.
     */
    public function updateVoucher(int $voucherId, array $data, int $userId): Voucher
    {
        $voucher = Voucher::findOrFail($voucherId);

        if ($voucher->isPosted()) {
            throw new Exception('Cannot update posted voucher');
        }

        $this->validateVoucherData($data, $voucher);

        $updateData = array_merge($data, [
            'updated_by' => $userId,
        ]);

        $voucher->update($updateData);

        return $voucher->fresh();
    }

    /**
     * Delete a voucher (soft delete).
     */
    public function deleteVoucher(int $voucherId, int $userId): bool
    {
        $voucher = Voucher::findOrFail($voucherId);

        if ($voucher->isPosted()) {
            throw new Exception('Cannot delete posted voucher');
        }

        $voucher->update([
            'updated_by' => $userId,
        ]);

        return $voucher->delete();
    }

    /**
     * Validate voucher data.
     */
    private function validateVoucherData(array $data, ?Voucher $voucher = null): void
    {
        // Validate voucher type (only for new vouchers or if type is being changed)
        if (isset($data['type'])) {
            $validTypes = [
                'sales', 'sales_return', 'purchase', 'purchase_return',
                'salary', 'expense', 'fixed_asset', 'depreciation',
            ];

            if (! in_array($data['type'], $validTypes)) {
                throw new Exception('Invalid voucher type');
            }
        }

        // Validate amount
        if (! isset($data['amount']) || ! is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new Exception('Amount must be positive');
        }

        // Validate required fields
        $requiredFields = ['date', 'amount', 'description'];

        // For new vouchers, type is required
        if (! $voucher) {
            $requiredFields[] = 'type';
        }

        foreach ($requiredFields as $field) {
            if (! isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Field {$field} is required");
            }
        }

        // Validate date format
        if (isset($data['date'])) {
            if (! strtotime($data['date'])) {
                throw new Exception('Invalid date format');
            }
        }

        // For updates, check if type is being changed
        if ($voucher && isset($data['type']) && $voucher->type !== $data['type']) {
            throw new Exception('Cannot change voucher type');
        }
    }

    /**
     * Get vouchers by organization with filters.
     */
    public function getVouchers(int $organizationId, array $filters = [])
    {
        $query = Voucher::where('organization_id', $organizationId);

        // Filter by type
        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            if ($filters['status'] === 'draft') {
                $query->draft();
            } elseif ($filters['status'] === 'posted') {
                $query->posted();
            }
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('date', '<=', $filters['date_to']);
        }

        // Order by date descending (newest first)
        $query->orderBy('date', 'desc')->orderBy('number', 'desc');

        return $query;
    }

    /**
     * Get voucher by ID with organization check.
     */
    public function getVoucherById(int $voucherId, int $organizationId): ?Voucher
    {
        return Voucher::where('id', $voucherId)
            ->where('organization_id', $organizationId)
            ->first();
    }
}
