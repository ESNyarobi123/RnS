<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->text('secret_key')->change();
        });
    }

    public function down(): void
    {
        Schema::table('bot_settings', function (Blueprint $table) {
            $table->string('secret_key', 255)->change();
        });
    }
};
