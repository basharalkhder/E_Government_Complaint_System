<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait GlobalTracing
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()              // يراقب كل الحقول في أي جدول
            ->logOnlyDirty()         // يسجل التعديلات الفعلية فقط
            ->useLogName('System_Global_Trace') 
            ->dontSubmitEmptyLogs()  // لا تسجل إذا تم الحفظ بدون تغيير
            ->setDescriptionForEvent(fn(string $eventName) => "Global Trace: This record has been {$eventName}");
    }
}