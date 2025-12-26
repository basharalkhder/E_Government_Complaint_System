<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\User;
use App\Models\ComplaintType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;


class ComplaintTypeService
{
    public function get_all_ComplaintTypes()
    {
        return Cache::remember('all_complaint_types', 3600, function () {
            return ComplaintType::with('entity')->get();
        });
    }

    public function getComplaintTypeById(int $id)
    {

        return Cache::remember("complaint_type_{$id}", 3600, function () use ($id) {
            return ComplaintType::with('entity')->findOrFail($id);
        });
    }


    public function createComplaintType(array $data)
    {
        // 1. البحث عن سجل محذوف ناعماً بنفس الكود أو الاسم
        $existingType = ComplaintType::withTrashed()
            ->where(function ($query) use ($data) {
                $query->where('code', $data['code'])
                    ->orWhere('name_ar', $data['name_ar']);
            })->first();

        if ($existingType) {
            if ($existingType->trashed()) {
                $existingType->restore();

                $existingType->update($data);
                return $existingType->load('entity');
            } else {

                throw new \Exception('This complaint type already exists and is active.');
            }
        }

        return ComplaintType::create($data)->load('entity');
    }

    public function updateComplaintType($data, $id)
    {

        $complaintType = $this->getComplaintTypeById($id);
        $complaintType->update($data);


        return $complaintType->load('entity');
    }

    public function deleteTypeComplaint($id)
    {
        $complaintType = $this->getComplaintTypeById($id);
        return $complaintType->delete();
    }
}
