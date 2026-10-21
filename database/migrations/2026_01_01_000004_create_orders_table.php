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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('order_number')->index();
            $table->uuid('customer_id');
            $table->string('bd_number');
            $table->date('order_date')->default(now()->toDateString());
            $table->date('delivery_date')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('advance', 12, 2)->default(0.00);
            $table->decimal('due', 12, 2)->default(0.00);
            $table->text('measurement_notes')->nullable();
            $table->text('extra_notes')->nullable();
            $table->enum('status', ['pending', 'delivered', 'due_payment'])->default('pending');
            $table->date('delivered_date')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->enum('order_category', ['officer', 'airman'])->default('officer');
            $table->string('branch')->nullable();
            $table->integer('ribon')->nullable();
            $table->string('running_year')->nullable();
            $table->string('trade')->nullable();
            $table->string('office')->nullable();
            $table->json('downloaded_categories')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index('status');
            $table->index('delivery_date');
            $table->index(['order_category', 'order_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
