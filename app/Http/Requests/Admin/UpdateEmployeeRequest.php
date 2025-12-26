<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
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
       
        $employeeId = $this->route('employee');

        return [
            'name' => 'sometimes|string|max:255',
            
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($employeeId)
            ],

            'password' => 'nullable|string|min:8',

            'entity_id' => [
                'sometimes',
                'integer',
                Rule::exists('entities', 'id')->whereNull('deleted_at')
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already assigned to another employee.',
            'password.min' => 'The password must be at least 8 characters if you want to change it.',
            'entity_id.exists' => 'The selected entity is invalid or has been deleted.',
        ];
    }
}
