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
        // Add inventory_type column to products table
        Schema::table('products', function (Blueprint $table) {
            $table->enum('inventory_type', ['pos', 'reservation'])->default('pos')->after('image_url');
        });

        // Drop reservation tables as we're consolidating into products table
        Schema::dropIfExists('reservation_product_sizes');
        Schema::dropIfExists('reservation_products');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate reservation_products table
        Schema::create('reservation_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->unique();
            $table->string('name');
            $table->string('brand');
            $table->string('category');
            $table->string('color');
            $table->decimal('price', 10, 2);
            $table->string('sku')->unique();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Recreate reservation_product_sizes table
        Schema::create('reservation_product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_product_id')->constrained()->onDelete('cascade');
            $table->string('size');
            $table->integer('stock')->default(0);
            $table->decimal('price_adjustment', 8, 2)->default(0.00);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // Remove inventory_type column from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('inventory_type');
        });
    }
};
