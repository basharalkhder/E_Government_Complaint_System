<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityResource extends JsonResource
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
            'name_ar' => $this->name_ar, // "البلدية والنظافة العامة"
            'name_en' => $this->name_en, // "Municipality and Public Cleanliness"
            'code'    => $this->code,    // "CITYHALL"
            'email'   => $this->email,
        ];
    }
}
