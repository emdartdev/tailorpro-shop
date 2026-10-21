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
        Schema::create('item_catalog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0.00);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('airman_item_catalog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('worker_item_catalog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0.00);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_item_catalog');
        Schema::dropIfExists('airman_item_catalog');
        Schema::dropIfExists('item_catalog');
    }
};
