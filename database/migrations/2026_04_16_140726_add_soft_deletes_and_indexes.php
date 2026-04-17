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
        Schema::table('businesses', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('status');
            $table->index(['business_id', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes();
            $table->index(['business_id', 'is_active']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index(['business_id', 'is_active']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('status');
            $table->index(['business_id', 'status']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->index('status');
            $table->index(['business_id', 'worker_id']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['status']);
            $table->dropIndex(['business_id', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['business_id', 'is_active']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'is_active']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['business_id', 'status']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['business_id', 'worker_id']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });
    }
};
