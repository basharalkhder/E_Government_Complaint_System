<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComplaintTypeRequest extends FormRequest
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
        $complaintTypeId = $this->route('complaint_type');

        return [
            'name_ar' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('complaint_types', 'name_ar')->ignore($complaintTypeId)
            ],
            'code' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('complaint_types', 'code')->ignore($complaintTypeId)
            ],
            'related_department' => 'nullable|string|max:150',
            'entity_id' => [
                'sometimes',
                'integer',
                Rule::exists('entities', 'id')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.unique'   => 'This complaint type name is already in use by another record.',
            'code.unique'      => 'This code is already assigned to another complaint type.',
            'code.max'         => 'The code must not exceed 20 characters.',
            'entity_id.exists' => 'The selected entity does not exist.',
        ];
    }
}
