<?php

namespace App\Http\Resources\Employee;

use App\Http\Resources\Citizen\LoginResource;
use App\Http\Resources\ComplaintEntityResource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetComplaintsResource extends JsonResource
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
            'reference_number' => $this->reference_number, // سهل الوصول إليه
            'status' => $this->status,
            'type_code' => $this->complaint_type_code,
            'department' => $this->department,
            'description' => $this->description,

            'location' => [
                'address' => $this->location_address,
                'lat' => $this->latitude,
                'lon' => $this->longitude,
            ],

            'submission_date' => $this->created_at->format('Y-m-d H:i:s'),

            'employee_notes' => $this->admin_notes,

            'complainant' => new LoginResource($this->whenLoaded('user')),
            'responsible_entity' => new ComplaintEntityResource($this->whenLoaded('entity')),

        ];
    }
}
