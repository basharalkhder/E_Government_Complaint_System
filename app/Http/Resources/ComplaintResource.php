<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
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
            'status' => $this->status,
            'description' => $this->description,
            'admin_notes' => $this->admin_notes,
            'location_address' => $this->location_address,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),

            'owner' => UserResource::make($this->whenLoaded('user')),

            
            'entity' => EntityResource::make($this->whenLoaded('entity')),
            
            'attachments' => $this->whenLoaded('attachments', function () {
              
                return $this->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_name' => $attachment->file_name,
                        // تأكد أن file_path يحتوي على URL العام
                        'file_url' => $attachment->file_path, 
                    ];
                });
            }),

            
            'histories' => ComplaintHistoryResource::collection($this->whenLoaded('histories')),
        ];
    }
}
