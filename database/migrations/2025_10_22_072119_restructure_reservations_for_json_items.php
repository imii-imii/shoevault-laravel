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
        Schema::table('reservations', function (Blueprint $table) {
            // Remove individual product columns
            $table->dropColumn([
                'product_id',
                'product_name', 
                'product_brand',
                'product_size',
                'product_color',
                'product_price',
                'quantity'
            ]);
            
            // Add JSON column for storing multiple items
            $table->json('items')->after('reservation_id');
            
            // Keep total_amount for the entire reservation
            // Keep all customer and reservation metadata
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Remove JSON column
            $table->dropColumn('items');
            
            // Restore individual product columns
            $table->unsignedBigInteger('product_id')->after('reservation_id');
            $table->string('product_name')->after('product_id');
            $table->string('product_brand')->after('product_name');
            $table->string('product_size')->after('product_brand');
            $table->string('product_color')->after('product_size');
            $table->decimal('product_price', 10, 2)->after('product_color');
            $table->integer('quantity')->after('product_price');
        });
    }
};
