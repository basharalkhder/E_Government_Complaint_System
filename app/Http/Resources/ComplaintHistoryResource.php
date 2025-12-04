<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintHistoryResource extends JsonResource
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
            'action_type' => $this->action_type, 
            'field_name' => $this->field_name,  
            'old_value' => $this->old_value,     
            'new_value' => $this->new_value,    
            'comment' => $this->comment,         
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),

            'actor' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
