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
        // Create products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->unique(); // Unique product identifier (SV-XXX-XXXX format)
            $table->string('name');
            $table->string('brand');
            $table->enum('category', ['men', 'women', 'accessories']);
            $table->string('color')->nullable(); // Product color
            $table->decimal('price', 10, 2);
            $table->string('sku')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('min_stock')->default(5); // Minimum stock threshold
            $table->timestamps();
        });

        // Create product_sizes table for size-specific inventory
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('size'); // Size value (e.g., '7', '8', 'M', 'L', 'One Size')
            $table->integer('stock')->default(0); // Stock quantity for this specific size
            $table->decimal('price_adjustment', 8, 2)->default(0.00); // Optional price adjustment for specific sizes
            $table->boolean('is_available')->default(true); // Whether this size is currently available
            $table->timestamps();
            
            // Ensure unique combination of product and size
            $table->unique(['product_id', 'size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sizes');
        Schema::dropIfExists('products');
    }
};
