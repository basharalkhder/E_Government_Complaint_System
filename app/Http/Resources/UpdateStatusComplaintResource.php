<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateStatusComplaintResource extends JsonResource
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
            'type' => $this->complaint_type_code, 
            'description' => $this->description,
            
            'status' => $this->status->value,
            'admin_notes' => $this->admin_notes,
            'department' => $this->department ?? 'Not Assigned',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            
            'citizen' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],

            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'address' => $this->location_address,
            ],
        ];
    }
}
