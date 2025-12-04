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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            
            // المفتاح الخارجي للمستخدم (ORM وعلاقة BelongsTo)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->foreignId('entity_id')->nullable()->constrained('entities');

            // الرمز الداخلي لنوع الشكوى (لربط الشكوى بنوعها)
            $table->string('complaint_type_code', 20);
            $table->foreign('complaint_type_code')
                ->references('code')
                ->on('complaint_types');

            $table->string('department', 150);
            $table->text('description');
            $table->enum('status', [
                'New',          // جديدة
                'In Progress',  // قيد المعالجة
                'Resolved',     // منجزة
                'Rejected',   // مرفوضة
                'Requested Info'
            ])->default('New');
            $table->text('admin_notes')->nullable();

            $table->boolean('is_locked')->default(false);;
            
            $table->foreignId('locked_by_user_id')
                  ->nullable() 
                  ->constrained('users')
                  ->onDelete('set null');

            // 3. وقت الحجز (لتنفيذ آلية انتهاء المهلة الزمنية)
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
