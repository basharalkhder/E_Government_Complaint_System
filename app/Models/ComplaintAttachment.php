<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintAttachment extends Model
{

    protected $fillable = [
        'complaint_id',
        'file_path',
        'file_name',
        'file_type',
    ];

    
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
