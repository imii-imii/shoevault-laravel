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
            // Drop the old id column if it exists and add reservation_id as primary key
            if (Schema::hasColumn('reservations', 'id')) {
                $table->dropPrimary(['id']);
                $table->dropColumn('id');
            }
            
            // Add reservation_id as primary key if it doesn't exist
            if (!Schema::hasColumn('reservations', 'reservation_id')) {
                $table->string('reservation_id')->primary();
            }
            
            // Add customer_id as foreign key if it doesn't exist
            if (!Schema::hasColumn('reservations', 'customer_id')) {
                $table->unsignedBigInteger('customer_id');
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            }
            
            // Add items column if it doesn't exist
            if (!Schema::hasColumn('reservations', 'items')) {
                $table->json('items');
            }
            
            // Add total_amount if it doesn't exist
            if (!Schema::hasColumn('reservations', 'total_amount')) {
                $table->decimal('total_amount', 10, 2);
            }
            
            // Add pickup_date if it doesn't exist
            if (!Schema::hasColumn('reservations', 'pickup_date')) {
                $table->date('pickup_date');
            }
            
            // Add pickup_time if it doesn't exist
            if (!Schema::hasColumn('reservations', 'pickup_time')) {
                $table->time('pickup_time');
            }
            
            // Add reserved_at if it doesn't exist
            if (!Schema::hasColumn('reservations', 'reserved_at')) {
                $table->timestamp('reserved_at')->useCurrent();
            }
            
            // Add status if it doesn't exist
            if (!Schema::hasColumn('reservations', 'status')) {
                $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            }
            
            // Add notes if it doesn't exist
            if (!Schema::hasColumn('reservations', 'notes')) {
                $table->text('notes')->nullable();
            }
            
            // Drop old columns that might exist from previous structure
            $columnsToDropIfExist = ['customer_email', 'customer_phone', 'product_id', 'product_name', 'product_brand', 'product_category', 'product_color', 'product_price', 'product_size', 'quantity', 'subtotal'];
            
            foreach ($columnsToDropIfExist as $column) {
                if (Schema::hasColumn('reservations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // This is a complex migration, so we'll just note what it would reverse
            // In a real scenario, you might want to backup data before running this
            
            // Add back old columns if needed
            $table->id();
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('product_id');
            $table->string('product_name');
            $table->string('product_brand');
            $table->string('product_category');
            $table->string('product_color');
            $table->decimal('product_price', 10, 2);
            $table->string('product_size');
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            
            // Drop new columns
            $newColumns = ['reservation_id', 'customer_id', 'items', 'total_amount', 'pickup_date', 'pickup_time', 'reserved_at', 'status', 'notes'];
            
            foreach ($newColumns as $column) {
                if (Schema::hasColumn('reservations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
