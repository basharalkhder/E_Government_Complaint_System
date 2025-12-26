<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GlobalTracing;

class Role extends Model
{
    use GlobalTracing;
    
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
