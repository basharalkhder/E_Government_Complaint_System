<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{

    use HasFactory;

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
}
