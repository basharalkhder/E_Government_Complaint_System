<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\User;
use App\Models\ComplaintType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;


class ManageUserService
{

    public function find_user($id)
    {
        return User::where('role_id', 3)->findOrFail($id);
    }
    public function getAllUsers()
    {
        return Cache::remember('admin_citizens_list', 3600, function () {
            return User::where('role_id', 3)
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function block_user($id)
    {
        $user = $this->find_user($id);

        $user->update([
            'is_active' => false
        ]);

        return $user;
    }

    public function unblock_user($id)
    {
        $user = $this->find_user($id);

        $user->update([
            'is_active' => true
        ]);

        return $user;
    }
}
