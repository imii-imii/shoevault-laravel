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
        Schema::table('product_sizes', function (Blueprint $table) {
            // Drop the current product_id column (integer)
            $table->dropColumn('product_id');
        });
        
        Schema::table('product_sizes', function (Blueprint $table) {
            // Add new product_id column as string to match products.product_id
            $table->string('product_id')->after('id');
            
            // Add foreign key constraint referencing products.product_id (string)
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_sizes', function (Blueprint $table) {
            // Drop the string foreign key constraint
            $table->dropForeign(['product_id']);
            
            // Drop the string product_id column
            $table->dropColumn('product_id');
        });
        
        Schema::table('product_sizes', function (Blueprint $table) {
            // Add back the integer product_id column
            $table->foreignId('product_id')->after('id')->constrained('products')->onDelete('cascade');
        });
    }
};
