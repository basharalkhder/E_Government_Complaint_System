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
                'Rejected'      // مرفوضة
            ])->default('New');
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
