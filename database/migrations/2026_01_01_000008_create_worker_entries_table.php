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
        Schema::create('worker_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('worker_id');
            $table->date('entry_date')->default(now()->toDateString());
            $table->date('khoroch_date')->nullable();
            $table->integer('serial')->nullable();
            $table->string('name')->default('');
            $table->string('item')->default('');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('total_taka', 12, 2)->default(0.00);
            $table->decimal('khoroch', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->index('worker_id');
            $table->index('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_entries');
    }
};
