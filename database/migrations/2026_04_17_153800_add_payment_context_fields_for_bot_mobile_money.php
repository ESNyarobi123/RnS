<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('paid_at');
            $table->string('provider_order_id')->nullable()->after('provider');
            $table->string('customer_phone')->nullable()->after('provider_order_id');
            $table->string('customer_name')->nullable()->after('customer_phone');
            $table->json('metadata')->nullable()->after('customer_name');
            $table->index('provider_order_id');
        });

        Schema::table('tips', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('worker_id')->constrained('payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tips', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['provider_order_id']);
            $table->dropColumn(['provider', 'provider_order_id', 'customer_phone', 'customer_name', 'metadata']);
        });
    }
};
