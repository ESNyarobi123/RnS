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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // business, worker, table
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('worker_id')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->string('name')->nullable(); // Table name or worker name
            $table->text('qr_image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('worker_id')->references('id')->on('business_workers')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
