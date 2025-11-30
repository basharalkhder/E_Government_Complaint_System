<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Entity;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entities = [
            [
                'name_ar' => 'وزارة الداخلية',
                'name_en' => 'Ministry of Interior',
                'code' => 'MOI',
                'email' => 'contact@moi.gov',
            ],
            [
                'name_ar' => 'البلدية والنظافة العامة',
                'name_en' => 'Municipality and Public Cleanliness',
                'code' => 'CITYHALL',
                'email' => 'info@cityhall.gov',
            ],
            [
                'name_ar' => 'هيئة تنظيم الكهرباء والمياه',
                'code' => 'ERA',
                'email' => 'era@regulators.gov',
            ],
            [
                'name_ar' => 'وزارة الاتصالات وتقنية المعلومات',
                'code' => 'MCIT',
                'email' => 'support@mcit.gov',
            ],
        ];

        foreach ($entities as $data) {
            // نستخدم firstOrCreate لتجنب تكرار الجهات إذا تم تشغيل Seeder أكثر من مرة
            Entity::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
