<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEntityRequest extends FormRequest
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

        $entityId = $this->route('entity');

        return [
            // قواعد التحقق من البيانات
            'name_ar' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('entities', 'name_ar')->ignore($entityId),
            ],
            'name_en' => 'nullable|string|max:255',
            'code'      => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('entities', 'code')->ignore($entityId),
            ],
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.string'   => 'The entity name must be a valid string.',
            'name_ar.max'      => 'The entity name may not be greater than 255 characters.',
            'name_ar.unique'   => 'This entity name has already been registered.',

            
            'code.string'      => 'The entity code must be a string.',
            'code.max'         => 'The entity code may not be greater than 50 characters.',
            'code.unique'      => 'This entity code is already assigned to another entity.',
            

            'email.email'      => 'Please provide a valid email address.',
            'email.max'        => 'The email address is too long.',

            'is_active.boolean' => 'The activation status must be true or false.',

            'notes.string'     => 'The notes field must be a text string.'
        ];
    }
}
