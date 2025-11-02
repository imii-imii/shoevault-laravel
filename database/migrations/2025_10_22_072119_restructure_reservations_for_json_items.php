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
            // First add the reservation_id column if it doesn't exist
            if (!Schema::hasColumn('reservations', 'reservation_id')) {
                $table->string('reservation_id')->unique()->after('id');
            }
            
            // Add missing product columns first (that will be dropped later)
            if (!Schema::hasColumn('reservations', 'product_name')) {
                $table->string('product_name')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('reservations', 'product_brand')) {
                $table->string('product_brand')->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('reservations', 'product_size')) {
                $table->string('product_size')->nullable()->after('product_brand');
            }
            if (!Schema::hasColumn('reservations', 'product_color')) {
                $table->string('product_color')->nullable()->after('product_size');
            }
            if (!Schema::hasColumn('reservations', 'product_price')) {
                $table->decimal('product_price', 10, 2)->nullable()->after('product_color');
            }
            if (!Schema::hasColumn('reservations', 'quantity')) {
                $table->integer('quantity')->nullable()->after('product_price');
            }
        });
        
        // Second migration step - now drop the columns and add JSON
        Schema::table('reservations', function (Blueprint $table) {
            // Remove individual product columns (now they exist)
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
