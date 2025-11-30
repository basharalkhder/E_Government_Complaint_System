<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEntityRequest extends FormRequest
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
            // قواعد التحقق من البيانات
            'name_ar' => 'required|string|max:255|unique:entities,name_ar',
            'name_en' => 'nullable|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('entities', 'code')],
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'notes' => 'nullable|string', // قد يكون هذا الحقل مطلوباً إذا كان يُرسل مع البيانات
        ];
    }

    public function messages(): array
    {
        return [
            'name_ar.required' => 'The entity name (Arabic) is required.',
            'name_ar.unique' => 'The entity name is already taken.',
            'code.required' => 'The entity code is required.',
            'code.unique' => 'The entity code is already in use.',
            'email.email' => 'The email format is invalid.',
            'is_active.boolean' => 'The activation status must be a boolean value.',
        ];
    }
}
