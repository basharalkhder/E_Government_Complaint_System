<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GlobalTracing;

class ComplaintHistory extends Model
{
    use GlobalTracing;
    
    protected $fillable = [
        'complaint_id',      // ربط الشكوى
        'user_id',           // المستخدم الذي قام بالإجراء
        'action_type',       // نوع الإجراء (STATUS_CHANGE, NOTE_ADDED, ATTACHMENT_ADDED)
        'field_name',        // الحقل الذي تم تغييره (status, admin_notes, file_name)
        'old_value',         // القيمة القديمة
        'new_value',         // القيمة الجديدة
        'comment',           // الملاحظة/الوصف التفصيلي
    ];

    // تعريف العلاقة مع الشكوى (للوصول إلى الشكوى من سجل التاريخ)
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    // تعريف العلاقة مع المستخدم (لمعرفة من قام بالإجراء)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
