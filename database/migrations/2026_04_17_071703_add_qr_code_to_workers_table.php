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
        Schema::table('business_workers', function (Blueprint $table) {
            $table->string('qr_code')->unique()->nullable();
            $table->text('qr_image_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_workers', function (Blueprint $table) {
            $table->dropColumn(['qr_code', 'qr_image_path']);
        });
    }
};
