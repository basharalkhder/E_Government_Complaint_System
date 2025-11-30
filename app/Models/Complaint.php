<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{

    const STATUS_NEW = 'New';
    const STATUS_IN_PROCESS = 'In Progress';
    const STATUS_COMPLETED = 'Resolved';
    const STATUS_REJECTED = 'Rejected';

    const STATUS_REQUESTED_INFO = 'Requested Info';

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
        'admin_notes'
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
}
