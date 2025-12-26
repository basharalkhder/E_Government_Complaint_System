<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintTypeRequest extends FormRequest
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
            'name_ar' => [
                'required',
                'string',
                'max:100',
                Rule::unique('complaint_types')->whereNull('deleted_at')
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                
                Rule::unique('complaint_types')->whereNull('deleted_at')
            ],
            'related_department' => 'nullable|string|max:150',
            'entity_id' => [
                'required',
                'integer',
                Rule::exists('entities', 'id')->whereNull('deleted_at')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'The Arabic name for the complaint type is required.',
            'name_ar.unique' => 'This complaint type name is already in use.',
            'code.required' => 'The complaint type code is required.',
            'code.unique' => 'This complaint type code is already in use.',
            'code.max' => 'The code must not exceed 20 characters.',
            'entity_id.required' => 'The responsible entity must be specified.',
            'entity_id.exists' => 'The selected responsible entity does not exist.',
        ];
    }
}
