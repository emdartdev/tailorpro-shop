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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('phone');
            $table->string('bd_number');
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            $table->timestamps();

            $table->index('phone');
            $table->index('bd_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
