<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateComplaintResource extends JsonResource
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
            'reference_number' => $this->reference_number,
            'status' => $this->status->value, 
            'type' => $this->complaint_type_code,
            'details' => [
                'description' => $this->description,
                'department' => $this->department,
                'responsible_entity' => new EntityResource($this->whenLoaded('entity')),
            ],
            'location' => [
                'address' => $this->location_address ?? 'Not Assigned',
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            
            'submitted_at' => $this->created_at->format('Y-m-d H:i'),
            
            
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            
           
        ];
    }
}
