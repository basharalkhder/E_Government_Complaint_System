<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\GlobalTracing;

class Entity extends Model
{
    use GlobalTracing;

    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'email',
        'is_active',
        'notes'
    ];

    /**
     * العلاقة: الجهة لديها العديد من الموظفين (Employees).
     */
    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'entity_id');
    }

    /**
     * العلاقة: الجهة مسؤولة عن العديد من الشكاوى.
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'entity_id');
    }


   protected static function booted()
{
    $clearCache = function ($entity) {
        \Illuminate\Support\Facades\Cache::forget('all_entities_list');
        \Illuminate\Support\Facades\Cache::forget("entity_{$entity->id}");
        \Illuminate\Support\Facades\Cache::forget('all_complaint_types');
    };

    static::saved($clearCache);
    static::deleted($clearCache);
}
}
