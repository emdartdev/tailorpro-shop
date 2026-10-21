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
        Schema::table('worker_entries', function (Blueprint $table) {
            $table->index(['worker_id', 'entry_date'], 'idx_worker_entries_worker_date');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['customer_id', 'created_at'], 'idx_orders_customer_created');
            $table->index(['status', 'order_category'], 'idx_orders_status_category');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('phone', 'idx_customers_phone');
            $table->index('name', 'idx_customers_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_entries', function (Blueprint $table) {
            $table->dropIndex('idx_worker_entries_worker_date');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_customer_created');
            $table->dropIndex('idx_orders_status_category');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_phone');
            $table->dropIndex('idx_customers_name');
        });
    }
};
