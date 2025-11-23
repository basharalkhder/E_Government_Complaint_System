<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'complaint_type_code' => 'required|exists:complaint_types,code',
            'description' => 'required|string|max:1000',
            'department' => 'sometimes|nullable|string|max:150',
            'location_address' => 'sometimes|nullable|string|max:255',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'attachments' => 'sometimes|array|max:5',
            'attachments.*' => 'file|mimes:pdf,jpg,png|max:5000',
        ];
    }
}
