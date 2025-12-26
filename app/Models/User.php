<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\GlobalTracing;

class User extends Authenticatable
{

    use GlobalTracing;
    
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'is_verified',
        'role_id',
        'entity_id',
    ];

    protected $casts = [
        'is_active' =>'boolean',
        'is_verified' =>'boolean'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee' && $this->entity_id !== null;
    }



    protected static function booted()
    {
        $clearCitizenCache = function ($user) {
        
            if ($user->role_id == 3) {
                \Illuminate\Support\Facades\Cache::forget('admin_citizens_list');
            }
        };

        static::saved($clearCitizenCache);

        static::deleted($clearCitizenCache);

    }
}
