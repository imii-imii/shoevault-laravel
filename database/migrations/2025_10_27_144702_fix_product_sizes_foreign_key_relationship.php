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
        // Get all foreign keys for product_sizes table
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'product_sizes' 
            AND COLUMN_NAME = 'product_id' 
            AND TABLE_SCHEMA = DATABASE()
            AND CONSTRAINT_NAME != 'PRIMARY'
        ");
        
        // Drop any existing foreign key constraints on product_id
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE product_sizes DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                // Continue if foreign key doesn't exist
            }
        }
        
        // Check if product_id column exists and is the right type
        $columns = DB::select("SHOW COLUMNS FROM product_sizes WHERE Field = 'product_id'");
        
        if (count($columns) > 0) {
            $column = $columns[0];
            // If it's not the right type, we need to recreate it
            if (strpos($column->Type, 'bigint') === false || strpos($column->Type, 'unsigned') === false) {
                Schema::table('product_sizes', function (Blueprint $table) {
                    $table->dropColumn('product_id');
                });
                
                Schema::table('product_sizes', function (Blueprint $table) {
                    $table->unsignedBigInteger('product_id')->after('id');
                });
            }
        } else {
            // Column doesn't exist, add it
            Schema::table('product_sizes', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->after('id');
            });
        }
        
        // Add the foreign key constraint
        try {
            Schema::table('product_sizes', function (Blueprint $table) {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_sizes', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['product_id']);
            
            // Drop the product_id column
            $table->dropColumn('product_id');
        });
        
        Schema::table('product_sizes', function (Blueprint $table) {
            // Add back the original product_id column structure
            $table->foreignId('product_id')->after('id')->constrained('products')->onDelete('cascade');
        });
    }
};
