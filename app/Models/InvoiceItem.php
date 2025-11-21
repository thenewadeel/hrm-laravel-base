<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get organization that owns the invoice item.
     */
    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get invoice for the invoice item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calculate total amount.
     */
    protected function getTotalAmountAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Scope to get items by invoice.
     */
    public function scopeByInvoice($query, int $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }
}