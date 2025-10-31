<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Rename product_sizes.id to product_size_id
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->renameColumn('id', 'product_size_id');
        });
        
        // Step 2: Remove price_adjustment column from product_sizes
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->dropColumn('price_adjustment');
        });
        
        // Step 3: Clear transaction_items table (since structure is changing completely)
        DB::table('transaction_items')->delete();
        
        // Step 4: Drop existing columns and add new ones for transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            // Drop columns that don't exist in new structure
            if (Schema::hasColumn('transaction_items', 'product_id')) {
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('transaction_items', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('transaction_items', 'total')) {
                $table->dropColumn('total');
            }
        });
        
        // Step 5: Add new columns for transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            // Add new columns
            $table->string('product_name')->after('product_size_id');
            $table->string('product_brand')->after('product_name');
            $table->string('product_color')->after('product_brand');
            $table->string('product_category')->after('product_color');
            $table->string('size')->after('quantity');
            $table->decimal('unit_price', 10, 2)->after('size');
            $table->decimal('cost_price', 10, 2)->nullable()->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse Step 5: Remove new columns from transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'product_brand', 'product_color', 'product_category', 'size', 'unit_price', 'cost_price']);
        });
        
        // Reverse Step 4: Add back old columns to transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->after('product_size_id');
            $table->decimal('price', 10, 2)->after('quantity');
            $table->decimal('total', 10, 2)->after('price');
        });
        
        // Reverse Step 2: Add back price_adjustment to product_sizes
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->decimal('price_adjustment', 8, 2)->default(0.00)->after('stock');
        });
        
        // Reverse Step 1: Rename product_size_id back to id
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->renameColumn('product_size_id', 'id');
        });
    }
};
