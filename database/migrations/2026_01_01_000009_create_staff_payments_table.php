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
        Schema::create('staff_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('worker_id');
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->date('payment_date')->default(now()->toDateString());
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->index('worker_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_payments');
    }
};
