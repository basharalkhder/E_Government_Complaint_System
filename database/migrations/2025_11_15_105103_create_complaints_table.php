<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ComplaintStatus;

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
                ->on('complaint_types')->onUpdate('cascade');;

            $table->string('department', 150);
            $table->text('description');
            $table->enum('status', array_column(ComplaintStatus::cases(), 'value'))
                ->default(ComplaintStatus::NEW->value);
            $table->text('admin_notes')->nullable();

            
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
