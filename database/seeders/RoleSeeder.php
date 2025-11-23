<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'admin', 'description' => 'مدير النظام، مسؤول عن إنشاء حسابات الموظفين'],
            ['name' => 'employee', 'description' => 'موظف إداري، مسؤول عن تحديث حالات الشكاوى'],
            ['name' => 'citizen', 'description' => 'المواطن، مسؤول عن تقديم ومتابعة الشكاوى'],
        ]);
    }
}
