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
        Schema::table('orders', function (Blueprint $table) {
            $table->index('order_date', 'idx_orders_order_date');
            $table->index('bd_number', 'idx_orders_bd_number');
            $table->index(['order_category', 'order_date'], 'idx_orders_category_order_date');
            $table->index(['order_category', 'running_year'], 'idx_orders_category_running_year');
            $table->index(['order_category', 'delivery_date'], 'idx_orders_category_delivery_date');
        });

        Schema::table('payment_history', function (Blueprint $table) {
            $table->index(['order_id', 'payment_type'], 'idx_ph_order_type');
            $table->index(['created_at', 'payment_type'], 'idx_ph_created_type');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('item_name', 'idx_order_items_item_name');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('bd_number', 'idx_customers_bd_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_order_date');
            $table->dropIndex('idx_orders_bd_number');
            $table->dropIndex('idx_orders_category_order_date');
            $table->dropIndex('idx_orders_category_running_year');
            $table->dropIndex('idx_orders_category_delivery_date');
        });

        Schema::table('payment_history', function (Blueprint $table) {
            $table->dropIndex('idx_ph_order_type');
            $table->dropIndex('idx_ph_created_type');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_item_name');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_bd_number');
        });
    }
};
