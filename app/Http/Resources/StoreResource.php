<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'location' => $this->location,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'organization_unit_id' => $this->organization_unit_id,
            'organization_id' => $this->whenLoaded('organization_unit', function () {
                return $this->organization_unit->organization_id;
            }),
            'organization' => $this->whenLoaded('organization_unit.organization', function () {
                return [
                    'id' => $this->organization_unit->organization->id,
                    'name' => $this->organization_unit->organization->name,
                ];
            }),
            'organization_unit' => $this->whenLoaded('organization_unit', function () {
                return [
                    'id' => $this->organization_unit->id,
                    'name' => $this->organization_unit->name,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items_count' => $this->whenCounted('items'),
            'items' => $this->whenLoaded('items', function () {
                return ItemResource::collection($this->items);
            }),
        ];
    }
}
