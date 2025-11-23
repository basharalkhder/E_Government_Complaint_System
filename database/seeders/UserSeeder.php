<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $employeeRole = Role::where('name', 'employee')->first();

       
        if ($adminRole) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@domain.com',
                'password' => Hash::make('password'), 
                'role_id' => $adminRole->id,
                'is_verified' => true,
            ]);
        }

        if ($employeeRole) {
            User::create([
                'name' => 'Employee One',
                'email' => 'employee@domain.com',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole->id,
                'is_verified' => true,
            ]);
        }
    }
}
