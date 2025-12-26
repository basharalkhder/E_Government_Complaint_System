<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GlobalTracing;

class ComplaintAttachment extends Model
{
    use GlobalTracing;

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
