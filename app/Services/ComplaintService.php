<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Events\ComplaintStatusUpdated;

class ComplaintService
{

    public function getUserComplaints()
    {
        $user = Auth::user();
        return $user->complaints;
    }


    public function createComplaint(int $userId, array $data): Complaint
    {

        $entityId = $this->determineResponsibleEntityId($data['complaint_type_code']);

        $referenceNumber = 'COMP-' . Str::upper(Str::random(8));

        $complaint = Complaint::create([
            'user_id' => $userId,
            'complaint_type_code' => $data['complaint_type_code'],
            'entity_id' => $entityId,
            'department' => $data['department'] ?? 'غير محدد',
            'description' => $data['description'],
            'location_address' => $data['location_address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'reference_number' => $referenceNumber,
            'status' => 'New',

        ]);


        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $this->saveAttachments($complaint, $data['attachments']);
        }
        return $complaint;
    }

    protected function determineResponsibleEntityId(string $complaintTypeCode): ?int
    {
        $complaintType = ComplaintType::where('code', $complaintTypeCode)
            // نختار فقط entity_id لتقليل حجم الاستعلام
            ->first(['entity_id']);

        // إذا وُجد نوع الشكوى، نُرجع الـ entity_id، وإلا نُرجع NULL
        return $complaintType->entity_id ?? null;
    }


    protected function saveAttachments(Complaint $complaint, array $attachments): void
    {
        $records = [];

        foreach ($attachments as $file) {
            // حفظ الملف في مجلد 'public/complaints/attachments'
            $path = $file->store('public/complaints/attachments');

            // استخدام Storage::url للحصول على المسار العام
            $url = Storage::url($path);

            $records[] = [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $url,
                'file_type' => $file->getClientOriginalExtension(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }


        $complaint->attachments()->createMany($records);
    }


    public function getComplaintTypes(): Collection
    {

        return Cache::remember('all_complaint_types', 3600, function () {

            return ComplaintType::all();
        });
    }


    public function updateComplaintStatus(int $complaintId, array $data, int $entityId): Complaint
    {
        
        $complaint = Complaint::findOrFail($complaintId);

        // التأكد من أن الشكوى تخص الجهة التي يعمل بها الموظف
        if ($complaint->entity_id !== $entityId) {
            // إلقاء استثناء (Exception) بدلاً من إرجاع Response
            throw new AuthorizationException('Unauthorized. This complaint does not belong to your entity.');
        }

        
        $updateData = [
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
        ];

        $complaint->update($updateData);

        
        event(new ComplaintStatusUpdated($complaint));

        return $complaint;
    }
}
