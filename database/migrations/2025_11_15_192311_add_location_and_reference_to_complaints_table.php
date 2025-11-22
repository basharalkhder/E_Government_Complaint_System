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
        Schema::table('complaints', function (Blueprint $table) {
            // 1. الرقم المرجعي
            $table->string('reference_number', 50)->unique()->after('status');

            // 2. الموقع
            $table->string('location_address')->nullable()->after('reference_number');
            $table->decimal('latitude', 10, 7)->nullable(); // دقة لخط العرض
            $table->decimal('longitude', 10, 7)->nullable(); // دقة لخط الطول
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn(['reference_number', 'location_address', 'latitude', 'longitude']);
        });
    }
};
