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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'low_stock', 'new_reservation', 'reservation_expiring', etc.
            $table->string('title'); // Short notification title
            $table->text('message'); // Detailed notification message
            $table->string('target_role'); // 'owner', 'manager', 'cashier', or 'all'
            $table->json('data')->nullable(); // Additional data (product_id, reservation_id, etc.)
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('icon')->default('fas fa-bell'); // FontAwesome icon class
            $table->string('priority')->default('normal'); // 'low', 'normal', 'high', 'urgent'
            $table->timestamps();
            
            // Indexes for efficient querying
            $table->index(['target_role', 'is_read', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
