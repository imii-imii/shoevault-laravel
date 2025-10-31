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
        // Drop the existing reservations table if it exists
        Schema::dropIfExists('reservations');
        
        // Create the reservations table with the correct structure
        Schema::create('reservations', function (Blueprint $table) {
            $table->string('reservation_id')->primary();
            $table->string('customer_id', 20);
            $table->json('items');
            $table->decimal('total_amount', 10, 2);
            $table->date('pickup_date');
            $table->time('pickup_time');
            $table->timestamp('reserved_at')->useCurrent();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('customer_id');
            $table->index('status');
            $table->index('pickup_date');
            $table->index('reserved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
