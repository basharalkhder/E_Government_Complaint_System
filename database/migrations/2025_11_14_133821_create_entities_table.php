<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            // اسم الجهة (مطلوب)
            $table->string('name_ar')->unique();
            $table->string('name_en')->nullable();

            // رمز الجهة (فريد، يستخدم للاستعلام السريع)
            $table->string('code', 50)->unique();

            // معلومات اتصال الجهة
            $table->string('email')->nullable();

            // حالة التفعيل الافتراضية للجهة
            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
