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
        Schema::table('waiter_calls', function (Blueprint $table) {
            $table->foreignId('table_id')
                ->nullable()
                ->after('service_table_id')
                ->constrained('tables')
                ->nullOnDelete();
            $table->text('notes')->nullable()->after('customer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waiter_calls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('table_id');
            $table->dropColumn('notes');
        });
    }
};
