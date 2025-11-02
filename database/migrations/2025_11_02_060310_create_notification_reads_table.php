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
        Schema::create('notification_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id');
            $table->string('user_id'); // Changed to string to match users table
            $table->timestamp('read_at');
            $table->timestamps();
            
            // Ensure each user can only mark a notification as read once
            $table->unique(['notification_id', 'user_id']);
            
            // Index for efficient querying
            $table->index(['user_id', 'read_at']);
            $table->index(['notification_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};
