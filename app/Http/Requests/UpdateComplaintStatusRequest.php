<?php

namespace App\Http\Requests;

use App\Enums\ComplaintStatus; 
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateComplaintStatusRequest extends FormRequest
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
            'status' => ['required', new Enum(ComplaintStatus::class)],
            'admin_notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
       return [
        'status.required' => 'The complaint status field is required.',
        'status.Illuminate\Validation\Rules\Enum' => 'The selected status is invalid. Please provide a valid complaint status.',
        'admin_notes.string' => 'Administrative notes must be a valid string.',
        'admin_notes.max' => 'Administrative notes may not be greater than 1000 characters.',
    ];
    }
}
