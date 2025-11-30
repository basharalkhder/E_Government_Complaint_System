<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'entity_id' => [
                'required',
                'integer',
                Rule::exists('entities', 'id')
            ],
        ];

        
    }



    public function messages(): array
    {
        return [
            
            'name.required' => 'The employee name is required.',
            'email.required' => 'The email address is required.',
            'password.required' => 'The password is required.',
            
            
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.min' => 'The password must be at least 8 characters.',
            
            
            'entity_id.required' => 'The responsible entity must be selected.',
            'entity_id.exists' => 'The selected entity does not exist.',
        ];
    }
}
