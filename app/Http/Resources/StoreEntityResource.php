<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreEntityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'code'    => $this->code,
            'email'   => $this->email,
            'is_active' => $this->is_active,
            'notes' => $this->entits,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
