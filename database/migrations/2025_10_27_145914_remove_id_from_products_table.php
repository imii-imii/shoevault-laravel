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
        Schema::table('products', function (Blueprint $table) {
            // First, drop any foreign key constraints that reference products.id
            // We'll need to check if any exist and drop them manually if needed
            
            // Remove the auto-increment id column
            $table->dropColumn('id');
            
            // Make product_id the primary key
            $table->primary('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop the primary key on product_id
            $table->dropPrimary(['product_id']);
            
            // Add back the auto-increment id column as primary key
            $table->id()->first();
        });
    }
};
