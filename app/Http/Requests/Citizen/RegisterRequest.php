<?php

namespace App\Http\Requests\Citizen;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required and cannot be left empty.',
            'name.max' => 'The instructor name must not exceed :255 characters.',

            'email.required' => 'The email field is required.',
            'email.email' => 'The email format is invalid.',
            'email.unique' => 'This email is already registered.',

            'password.required' =>'The password field is required.',
            'password.min' =>'The password must be at least 8 characters long.'

        ];
    }
}
