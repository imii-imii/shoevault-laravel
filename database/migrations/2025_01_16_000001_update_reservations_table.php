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
            // Add new columns for better reservation tracking
            $table->string('reservation_id')->unique()->after('id');
            $table->string('product_name')->after('product_id');
            $table->string('product_brand')->after('product_name');
            $table->string('product_size')->after('product_brand');
            $table->string('product_color')->after('product_size');
            $table->decimal('product_price', 10, 2)->after('product_color');
            $table->decimal('total_amount', 10, 2)->after('quantity');
            $table->date('pickup_date')->after('total_amount');
            $table->string('pickup_time')->after('pickup_date');
            $table->timestamp('reserved_at')->nullable()->after('pickup_time');
            
            // Remove the old reservation_date column and replace with proper structure
            $table->dropColumn('reservation_date');
            
            // Rename size to product_size_selected for clarity
            $table->dropColumn('size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'reservation_id',
                'product_name',
                'product_brand', 
                'product_size',
                'product_color',
                'product_price',
                'total_amount',
                'pickup_date',
                'pickup_time',
                'reserved_at'
            ]);
            
            $table->date('reservation_date')->after('notes');
            $table->string('size')->after('quantity');
        });
    }
};