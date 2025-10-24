<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'type' => $this->type,
            'status' => $this->status,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'transaction_date' => $this->transaction_date,
            'finalized_at' => $this->finalized_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Computed attributes - convert from cents to dollars
            'total_quantity' => $this->total_quantity,
            'total_value' => $this->total_value,
            // 'total_quantity' => $this->whenNotNull($this->total_quantity),
            // 'total_value' => $this->whenNotNull($this->total_value ? $this->total_value / 100  : null),

            // Relationships
            'store' => $this->whenLoaded('store', function () {
                return [
                    'id' => $this->store->id,
                    'name' => $this->store->name,
                    'code' => $this->store->code,
                ];
            }),

            'created_by_user' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->name,
                    'email' => $this->createdBy->email,
                ];
            }),

            'approved_by_user' => $this->whenLoaded('approvedBy', function () {
                return $this->approvedBy ? [
                    'id' => $this->approvedBy->id,
                    'name' => $this->approvedBy->name,
                    'email' => $this->approvedBy->email,
                ] : null;
            }),

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'transaction_id' => $item->transaction_id,
                        'item_id' => $item->item_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price, // ✅ Convert cents to dollars
                        'total_price' => ($item->quantity * $item->unit_price), // ✅ Convert cents to dollars
                        'notes' => $item->notes,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,

                        'item' => $this->when($item->relationLoaded('item'), function () use ($item) {
                            return $item->item ? [
                                'id' => $item->item->id,
                                'name' => $item->item->name,
                                'sku' => $item->item->sku,
                                'description' => $item->item->description,
                                'category' => $item->item->category,
                                'unit' => $item->item->unit,
                            ] : null;
                        })
                    ];
                });
            }),

            // Summary information
            'items_count' => $this->whenCounted('items'),

            // Status flags
            'is_draft' => $this->status === 'draft',
            'is_finalized' => $this->status === 'finalized',
            'is_cancelled' => $this->status === 'cancelled',

            // Type flags
            'is_incoming' => $this->type === 'incoming',
            'is_outgoing' => $this->type === 'outgoing',
            'is_adjustment' => $this->type === 'adjustment',
        ];
    }
}
