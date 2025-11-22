<?php

namespace App\Repositories;

use App\Models\Complaint;
use App\Models\ComplaintType;
use App\Contracts\ComplaintRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EloquentComplaintRepository implements ComplaintRepositoryInterface
{

    public function createComplaint(int $userId, array $data): Complaint
    {
        $referenceNumber = 'COMP-' . Str::upper(Str::random(8));

        $complaint = Complaint::create([
            'user_id' => $userId,
            'complaint_type_code' => $data['complaint_type_code'],
            'department' => $data['department'] ?? 'غير محدد',
            'description' => $data['description'],
            'location_address' => $data['location_address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'reference_number' => $referenceNumber,
            'status' => 'New',
            
        ]);

        // 1. معالجة المرفقات (التحقق من وجود ملفات في الطلب)
        if (isset($data['attachments']) && is_array($data['attachments'])) {
            $this->saveAttachments($complaint, $data['attachments']);
        }
        return $complaint;
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
}
