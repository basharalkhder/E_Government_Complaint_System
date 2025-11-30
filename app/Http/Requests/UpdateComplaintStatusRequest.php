<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Complaint;

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
        // استخدام ثوابت الموديل لضمان أن القيم صالحة
        $allowedStatuses = implode(',', [
            Complaint::STATUS_IN_PROCESS,
            Complaint::STATUS_COMPLETED,
            Complaint::STATUS_REJECTED,
            Complaint::STATUS_REQUESTED_INFO,
        ]);

        return [
            'status' => 'required|in:' . $allowedStatuses,
            'admin_notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة الشكوى مطلوبة.',
            'status.in' => 'حالة الشكوى غير صالحة.',
            'admin_notes.string' => 'ملاحظات الإدارة يجب أن تكون نصاً.',
        ];
    }
}
