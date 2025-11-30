<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Entity;
use App\Models\ComplaintType;

class ComplaintTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        // 1. جلب IDs الجهات مسبقاً
        $entityIds = Entity::pluck('id', 'code')->toArray();

        $complaintTypes = [
            [
                'name_ar' => 'شكوى انقطاع التيار الكهربائي',
                'code' => 'ELEC_OUT',
                'related_department' => 'الشكاوى الفنية',
                // الربط: نستخدم entityIds['ERA'] للحصول على ID هيئة الكهرباء
                'entity_id' => $entityIds['ERA'] ?? null,
            ],
            [
                'name_ar' => 'شكوى سوء معاملة من موظف',
                'code' => 'ADMN_ABUSE',
                'related_department' => 'الرقابة الداخلية',
                // الربط: نستخدم entityIds['MOI'] أو جهة أخرى حسب السياسة
                'entity_id' => $entityIds['MOI'] ?? null,
            ],
            [
                'name_ar' => 'شكوى تراكم النفايات',
                'code' => 'CITY_WASTE',
                'related_department' => 'قسم النظافة',
                'entity_id' => $entityIds['CITYHALL'] ?? null,
            ],
            [
                'name_ar' => 'شكوى رداءة خدمة الإنترنت',
                'code' => 'INTERNET_QOS',
                'related_department' => 'الشكاوى التقنية',
                'entity_id' => $entityIds['MCIT'] ?? null,
            ],
        ];

        foreach ($complaintTypes as $data) {
            // نستخدم firstOrCreate لتجنب تكرار أنواع الشكاوى
            ComplaintType::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
