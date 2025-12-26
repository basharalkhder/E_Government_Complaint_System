<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name_ar, 
            'code'       => $this->code,
            'department' => $this->related_department,
            'responsible_entity' => new EntityResource($this->whenLoaded('entity')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            
        ];
    }
}
