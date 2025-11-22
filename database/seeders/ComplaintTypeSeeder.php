<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplaintTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       

        $types = [
            [
                'name_ar' => 'شكوى إدارية',
                'code' => 'ADMN',
                'related_department' => 'قسم الإدارة العامة',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'شكوى فساد مالية',
                'code' => 'FNCE',
                'related_department' => 'مديرية الرقابة المالية',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'شكوى نقص خدمة',
                'code' => 'SERV',
                'related_department' => 'قسم جودة الخدمات',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('complaint_types')->insert($types);
    }
}
