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
use App\Enums\ComplaintStatus;
use Exception;

class ComplaintService
{



    public function getUserComplaints()
    {
        $user = Auth::user();
        $complaints = $user->complaints()->with('entity')->get();
        return $complaints;
    }


    public function handleComplaintSubmission(int $userId, array $data): Complaint
    {

        $this->ensureNoRecentDuplicate($userId, $data['complaint_type_code']);


        $entityId = $this->determineResponsibleEntityId($data['complaint_type_code']);


        $referenceNumber = 'COMP-' . date('Ymd') . '-' . Str::upper(Str::random(4));

        $complaint = Complaint::create([
            'user_id'          => $userId,
            'complaint_type_code' => $data['complaint_type_code'],
            'entity_id'        => $entityId,
            'department'       => $data['department'] ?? 'غير محدد',
            'description'      => $data['description'],
            'location_address' => $data['location_address'] ?? null,
            'latitude'         => $data['latitude'] ?? null,
            'longitude'        => $data['longitude'] ?? null,
            'reference_number' => $referenceNumber,
            'status'           => 'New',
        ]);

        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $this->saveAttachments($complaint, $data['attachments'], $userId);
        }

        $complaint->load(['entity', 'attachments', 'user']);

        return $complaint;
    }

    protected function ensureNoRecentDuplicate(int $userId, string $typeCode): void
    {
        $exists = Complaint::where('user_id', $userId)
            ->where('complaint_type_code', $typeCode)
            ->whereIn('status', ['New', 'In Progress', 'Requested Info'])
            ->where('created_at', '>', now()->subDays(30))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'complaint_type_code' => 'You have a recently submitted active complaint regarding the same issue. Please follow up on your existing complaint.'
            ]);
        }
    }

    protected function determineResponsibleEntityId(string $complaintTypeCode): ?int
    {
        $complaintType = ComplaintType::where('code', $complaintTypeCode)

            ->first(['entity_id']);


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

            return ComplaintType::with('entity')->get();
        });
    }


    public function updateComplaintStatus(int $complaintId, array $data, int $entityId): Complaint
    {
        return DB::transaction(function () use ($complaintId, $data, $entityId) {

            
            $complaint = Complaint::lockForUpdate()->findOrFail($complaintId);


           
            
            if ($complaint->entity_id !== $entityId) {
                throw new AuthorizationException('Unauthorized. This complaint does not belong to your entity.');
            }

            if (isset($data['status']) && $complaint->status->value === $data['status']) {
                throw new \Exception('This complaint status has already been updated by another employee.');
            }

            
            if (isset($data['status']) && $complaint->status->value !== $data['status']) {
                $complaint->histories()->create([
                    'user_id' => Auth::id(),
                    'action_type' => 'STATUS_CHANGE',
                    'field_name' => 'status',
                    'old_value' => $complaint->status->value,
                    'new_value' => $data['status'],
                    'comment' => "Status updated from {$complaint->status->value} to {$data['status']}",
                ]);
            }

            if (isset($data['admin_notes']) && $complaint->admin_notes !== $data['admin_notes']) {
                $complaint->histories()->create([
                    'user_id' => Auth::id(),
                    'action_type' => 'NOTE_ADDED',
                    'field_name' => 'admin_notes',
                    'old_value' => $complaint->admin_notes,
                    'new_value' => $data['admin_notes'],
                    'comment' => "Administrative notes have been modified."
                ]);
            }

            // 5. تحديث البيانات
            $updateData = [
                'status' => $data['status'],
                'admin_notes' => $data['admin_notes'] ?? $complaint->admin_notes,
            ];

            $complaint->update($updateData);

            
            event(new ComplaintStatusUpdated($complaint));

            return $complaint;
        }); 
    }


    public function processUserUpdate(int $id, int $userId, array $data, ?array $files)
    {
        return DB::transaction(function () use ($id, $userId, $data, $files) {
            
            
            $complaint = Complaint::findOrFail($id);

            if ($complaint->user_id !== $userId) {
                throw new Exception('Unauthorized to update this complaint.', 403);
            }

            // 2. Guard Clause for Status (Case Insensitive & Trimmed)
            if ($complaint->status->value !== ComplaintStatus::REQUESTED_INFO->value) {
                throw new Exception('Updates allowed only when status is "Requested Info".', 422);
            }

            $updatePayload = [
                'status' => ComplaintStatus::IN_PROGRESS->value// التغيير التلقائي للحالة هنا
            ];

            // 3. Update main complaint data
            if (isset($data['description'])) {
                $complaint->update(['description' => $data['description']]);
            }

            // Save old status for history record
            $oldStatus = $complaint->status->value;

            // Update Complaint
            $complaint->update($updatePayload);

            ComplaintHistory::create([
                'complaint_id' => $complaint->id,
                'user_id'      => $userId,
                'action_type'  => 'STATUS_CHANGED',
                'field_name'   => 'status',
                'old_value'    => $oldStatus,
                'new_value'    => 'In Progress',
                'comment'      => "Status updated automatically to In Progress after user response.",
            ]);

            // 4. Handle Attachments and History if files exist
            if ($files) {
                $this->addAttachmentsAndHistory($complaint, $files, $userId);
            }

            return $complaint->load('attachments');
        });
    }

    protected function addAttachmentsAndHistory(Complaint $complaint, array $files, int $userId)
    {
        $attachmentRecords = [];
        $historyRecords = [];

        foreach ($files as $file) {
            // Store File
            $path = $file->store('complaints/attachments', 'public');
            $url = Storage::disk('public')->url($path);
            $fileName = $file->getClientOriginalName();

            // Prepare Attachment Record
            $attachmentRecords[] = [
                'file_name' => $fileName,
                'file_path' => $url,
                'file_type' => $file->getClientOriginalExtension(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Prepare History Record
            $historyRecords[] = [
                'complaint_id' => $complaint->id,
                'user_id'      => $userId,
                'action_type'  => 'ATTACHMENT_ADDED',
                'field_name'   => 'attachment',
                'new_value'    => $fileName,
                'comment'      => "Attachment added: {$fileName}",
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        // Batch Insert for better performance
        $complaint->attachments()->createMany($attachmentRecords);
        ComplaintHistory::insert($historyRecords);
    }
    



}
