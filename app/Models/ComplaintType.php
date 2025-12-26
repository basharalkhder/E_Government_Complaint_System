<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\GlobalTracing;

class ComplaintType extends Model
{
    use GlobalTracing;

    use SoftDeletes;

    protected $fillable = [
        'name_ar',
        'code',
        'related_department',
        'entity_id',
    ];


    /**
     * العلاقة: نوع الشكوى ينتمي لجهة مسؤولة واحدة.
     */
    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    protected static function booted()
    {
        $clearCache = function ($complaintType) {
            // 1. مسح الكاش الخاص بالقائمة الكاملة
            \Illuminate\Support\Facades\Cache::forget('all_complaint_types');

            // 2. مسح كاش هذا النوع تحديداً بناءً على الـ ID
            \Illuminate\Support\Facades\Cache::forget("complaint_type_{$complaintType->id}");
        };

        static::saved($clearCache);
        static::deleted($clearCache);
        static::restored($clearCache);
    }
}
