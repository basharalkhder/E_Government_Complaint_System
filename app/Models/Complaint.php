<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{

    protected $fillable = [
        'user_id',
        'complaint_type_code',
        'department',
        'description',
        'status',
        'location_address',
        'latitude',
        'longitude',
        'reference_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
