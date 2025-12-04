<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Models\ComplaintHistory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Events\ComplaintStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ComplaintService
{

    const LOCK_TIMEOUT_MINUTES = 15;

    public function acquireLock(Complaint $complaint): void
    {
        $currentUserId = Auth::id();

        if ($complaint->is_locked) {


            $lockedAt = $complaint->locked_at;
            $isTimedOut = $lockedAt && $lockedAt->addMinutes(self::LOCK_TIMEOUT_MINUTES)->isPast();


            if ($isTimedOut) {
                $this->releaseLock($complaint);
            } elseif ($complaint->locked_by_user_id !== $currentUserId) {

                $lockerName =  Auth::user()->name ?? 'موظف آخر';
                throw ValidationException::withMessages([
                    'lock' => "الشكوى محجوزة حالياً وقيد المعالجة من قبل {$lockerName}."
                ]);
            }
        }

        $complaint->update([
            'is_locked' => true,
            'locked_by_user_id' => $currentUserId,
            'locked_at' => Carbon::now(),
        ]);
    }


    public function releaseLock(Complaint $complaint): void
    {
        // نحرر القفل فقط إذا كان حائزه هو المستخدم الحالي (أو إذا كان القفل منتهي الصلاحية وتم تحريره في acquireLock)
        if ($complaint->locked_by_user_id === Auth::id() || $complaint->is_locked === true) {
            $complaint->update([
                'is_locked' => false,
                'locked_by_user_id' => null,
                'locked_at' => null,
            ]);
        }
    }




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
            $this->saveAttachments($complaint, $data['attachments'], $userId);
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


    public function saveAttachments(Complaint $complaint, array $attachments, int $uploadingUserId): void
    {
        $records = [];

        foreach ($attachments as $file) {
            $path = $file->store('complaints/attachments', 'public');

            $url = Storage::disk('public')->url($path);
            $fileName = $file->getClientOriginalName();

            $records[] = [
                'file_name' =>  $fileName,
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

        return DB::transaction(function () use ($complaintId, $data, $entityId) {


            $complaint = Complaint::findOrFail($complaintId);

            // تسجيل تغيير الحالة
            if (isset($data['status']) && $complaint->status !== $data['status']) {
                $complaint->histories()->create([
                    'user_id' => Auth::id(),
                    'action_type' => 'STATUS_CHANGE',
                    'field_name' => 'status',
                    'old_value' => $complaint->status,
                    'new_value' => $data['status'],
                    'comment' => "تغيير الحالة من {$complaint->status} إلى {$data['status']}",
                ]);
            }

            // تسجيل إضافة/تعديل الملاحظات الإدارية
            if (isset($data['admin_notes']) && $complaint->admin_notes !== $data['admin_notes']) {
                $complaint->histories()->create([
                    'user_id' => Auth::id(),
                    'action_type' => 'NOTE_ADDED',
                    'field_name' => 'admin_notes',
                    'old_value' => $complaint->admin_notes,
                    'new_value' => $data['admin_notes'],
                    'comment' => "تم إضافة/تعديل ملاحظة إدارية.",
                ]);
            }



            // التأكد من أن الشكوى تخص الجهة التي يعمل بها الموظف
            if ($complaint->entity_id !== $entityId) {
                // إلقاء استثناء (Exception) بدلاً من إرجاع Response
                throw new AuthorizationException('Unauthorized. This complaint does not belong to your entity.');
            }

            $this->acquireLock($complaint);

            try {
                $updateData = [
                    'status' => $data['status'],
                    'admin_notes' => $data['admin_notes'] ?? null,
                ];

                $complaint->update($updateData);


                event(new ComplaintStatusUpdated($complaint));
            } catch (\Exception $e) {
                $this->releaseLock($complaint);
                throw $e;
            }

            $this->releaseLock($complaint);

            return $complaint;
        });
    }


    public function updateComplaintByUser(Complaint $complaint, array $data, int $userId): Complaint
    {

        $updateFields = [];

        if (isset($data['description']) && $data['description'] !== $complaint->description) {
            $updateFields['description'] = $data['description'];

            // إنشاء سجل تاريخي لتغيير الوصف
            $complaint->histories()->create([
                'user_id' => $userId,
                'action_type' => 'FIELD_UPDATE', // نوع الإجراء: تحديث حقل
                'field_name' => 'description',
                'old_value' => substr($complaint->description ?? '', 0, 255), // قيمة قديمة (نقتصرها لـ 255 حرف)
                'new_value' => substr($data['description'], 0, 255), // قيمة جديدة
                'comment' => 'تم تحديث الوصف من قبل المستخدم.',
            ]);
        }

        // 4. الحفظ
        if (!empty($updateFields)) {
            $complaint->update($updateFields);
        }



        return $complaint;
    }



    public function addAttachmentsinhistory(Complaint $complaint, array $attachments, int $uploadingUserId)
    {
        $records = [];
        $historyRecords = []; // مصفوفة لتخزين سجلات التاريخ

        foreach ($attachments as $file) {
            // حفظ الملف في مجلد 'public/complaints/attachments'
            $path = $file->store('complaints/attachments', 'public');

            $url = Storage::disk('public')->url($path);
            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            $records[] = [
                'file_name' => $fileName,
                'file_path' => $url,
                'file_type' => $fileExtension,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // **إنشاء سجل تاريخي لإضافة المرفق**
            $historyRecords[] = [
                'complaint_id' => $complaint->id,
                'user_id' => $uploadingUserId,
                'action_type' => 'ATTACHMENT_ADDED', // نوع الإجراء: إضافة مرفق
                'field_name' => 'attachment',
                'old_value' => null,
                'new_value' => $fileName, // اسم الملف الجديد كقيمة جديدة
                'comment' => "تم إضافة المرفق: {$fileName} (النوع: {$fileExtension})",
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // حفظ المرفقات الجديدة في جدول المرفقات
        $complaint->attachments()->createMany($records);

        // **حفظ سجلات التاريخ دفعة واحدة**
        if (!empty($historyRecords)) {
            ComplaintHistory::insert($historyRecords);
        }
    }
}
