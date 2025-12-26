<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reference_number' => $this->reference_number, 
            'type'             => $this->complaint_type_code, 
            'responsible_entity' => new EntityResource($this->whenLoaded('entity')),
            'department'       => $this->department,
            'description'      => $this->description,
            'status'           => $this->status->value,
            'location'         => [
                'address'   => $this->location_address,
                'latitude'  => $this->latitude, 
                'longitude' => $this->longitude, 
            ],
            'admin_notes'      => $this->admin_notes ?? 'No comments', 
            'created_at'       => $this->created_at->format('Y-m-d H:i'), 
            
            
        ];
    }
}
