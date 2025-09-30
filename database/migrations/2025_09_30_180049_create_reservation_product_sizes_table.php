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
        Schema::create('reservation_product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_product_id')->constrained()->onDelete('cascade');
            $table->string('size');
            $table->integer('stock')->default(0);
            $table->decimal('price_adjustment', 8, 2)->default(0.00);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_product_sizes');
    }
};
