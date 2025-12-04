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
        Schema::create('complaint_histories', function (Blueprint $table) {
            $table->id();

            // 1. ربط الشكوى الأساسية
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');

            // 2. ربط المستخدم الذي قام بالإجراء
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // 3. نوع الإجراء (مثلاً: STATUS_CHANGE, NOTE_ADDED, ATTACHMENT_ADDED)
            $table->string('action_type', 50);

            // 4. التفاصيل القديمة والجديدة لغرض المقارنة
            $table->string('field_name', 50); // الحقل الذي تم تغييره (status, admin_notes, etc.)
            $table->string('old_value')->nullable(); // القيمة القديمة
            $table->string('new_value')->nullable(); // القيمة الجديدة

            // 5. ملاحظة/تعليق يوضح الإجراء (مثلاً: نص الملاحظة الإدارية الجديدة)
            $table->text('comment')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_histories');
    }
};
