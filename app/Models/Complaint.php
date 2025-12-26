<?php

namespace App\Models;


use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Model;

use App\Traits\GlobalTracing;


class Complaint extends Model
{
    use GlobalTracing;

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'status' => ComplaintStatus::class,

    ];


    protected $fillable = [
        'user_id',
        'entity_id',
        'complaint_type_code',
        'department',
        'description',
        'status',
        'location_address',
        'latitude',
        'longitude',
        'reference_number',
        'admin_notes',
        'is_locked',
        'locked_by_user_id',
        'locked_at'
    ];

    public function entity()
    {
        // يربط الشكوى بالجهة المسؤولة عن معالجتها
        return $this->belongsTo(Entity::class, 'entity_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function type()
    {

        return $this->belongsTo(ComplaintType::class, 'complaint_type_code', 'code');
    }

    public function attachments()
    {
        return $this->hasMany(ComplaintAttachment::class);
    }

    public function histories()
    {
        return $this->hasMany(ComplaintHistory::class, 'complaint_id');
    }
}
