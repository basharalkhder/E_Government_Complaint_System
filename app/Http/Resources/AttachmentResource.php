<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->file_name,
            'type'      => $this->file_type, // مثلاً png, pdf
            'file_url'  => asset($this->file_path), 
            'uploaded_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
