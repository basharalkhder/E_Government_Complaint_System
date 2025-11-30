<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintType extends Model
{
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
}
