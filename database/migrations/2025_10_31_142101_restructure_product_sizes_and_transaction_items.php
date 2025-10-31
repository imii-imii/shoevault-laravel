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
        // First, drop foreign key constraints that reference the old structure
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['product_size_id']);
        });
        
        // Check if there are other tables that reference product_sizes
        // Drop any foreign keys from other tables that might reference product_sizes
        if (Schema::hasTable('supply_logs')) {
            Schema::table('supply_logs', function (Blueprint $table) {
                $table->dropForeign(['product_size_id']);
            });
        }
        
        if (Schema::hasTable('reservation_product_sizes')) {
            Schema::table('reservation_product_sizes', function (Blueprint $table) {
                $table->dropForeign(['product_size_id']);
            });
        }
        
        // Backup existing data
        $productSizes = DB::table('product_sizes')->get();
        $transactionItems = DB::table('transaction_items')->get();
        
        // Drop and recreate product_sizes table
        Schema::dropIfExists('product_sizes');
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id('product_size_id'); // Renamed from 'id' to 'product_size_id'
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('size'); // Size value (e.g., '7', '8', 'M', 'L', 'One Size')
            $table->integer('stock')->default(0); // Stock quantity for this specific size
            // Removed price_adjustment column as requested
            $table->boolean('is_available')->default(true); // Whether this size is currently available
            $table->timestamps();
            
            // Ensure unique combination of product and size
            $table->unique(['product_id', 'size']);
        });
        
        // Restore product_sizes data
        foreach ($productSizes as $size) {
            DB::table('product_sizes')->insert([
                'product_size_id' => $size->id, // Map old 'id' to new 'product_size_id'
                'product_id' => $size->product_id,
                'size' => $size->size,
                'stock' => $size->stock,
                'is_available' => $size->is_available,
                'created_at' => $size->created_at,
                'updated_at' => $size->updated_at
            ]);
        }
        
        // Drop and recreate transaction_items table with new structure
        Schema::dropIfExists('transaction_items');
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID (don't need auto generated IDs)
            $table->string('transaction_id', 25); // From transactions table
            $table->unsignedBigInteger('product_size_id'); // From product_size table
            $table->string('product_name'); // Product name
            $table->string('product_brand'); // Product brand
            $table->string('product_color'); // Product color
            $table->string('product_category'); // Product category
            $table->integer('quantity'); // Quantity
            $table->string('size'); // Size
            $table->decimal('unit_price', 10, 2); // Unit price
            $table->decimal('cost_price', 10, 2)->nullable(); // Cost price
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
            $table->foreign('product_size_id')->references('product_size_id')->on('product_sizes')->onDelete('cascade');
        });
        
        // Recreate foreign keys for other tables
        if (Schema::hasTable('supply_logs')) {
            Schema::table('supply_logs', function (Blueprint $table) {
                $table->foreign('product_size_id')->references('product_size_id')->on('product_sizes')->onDelete('cascade');
            });
        }
        
        if (Schema::hasTable('reservation_product_sizes')) {
            Schema::table('reservation_product_sizes', function (Blueprint $table) {
                $table->foreign('product_size_id')->references('product_size_id')->on('product_sizes')->onDelete('cascade');
            });
        }
        
        // Note: We won't restore transaction_items data as the structure has changed significantly
        // The new transaction creation will handle the new format
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup current data
        $productSizes = DB::table('product_sizes')->get();
        
        // Drop current tables
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('product_sizes');
        
        // Recreate old product_sizes structure
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id(); // Back to 'id' instead of 'product_size_id'
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('size');
            $table->integer('stock')->default(0);
            $table->decimal('price_adjustment', 8, 2)->default(0.00); // Restore price_adjustment
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            $table->unique(['product_id', 'size']);
        });
        
        // Restore product_sizes data
        foreach ($productSizes as $size) {
            DB::table('product_sizes')->insert([
                'id' => $size->product_size_id, // Map back to 'id'
                'product_id' => $size->product_id,
                'size' => $size->size,
                'stock' => $size->stock,
                'price_adjustment' => 0.00, // Default value since it was removed
                'is_available' => $size->is_available,
                'created_at' => $size->created_at,
                'updated_at' => $size->updated_at
            ]);
        }
        
        // Recreate old transaction_items structure
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 25);
            $table->unsignedBigInteger('product_size_id');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
            
            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
            $table->foreign('product_size_id')->references('id')->on('product_sizes')->onDelete('cascade');
        });
    }
};
