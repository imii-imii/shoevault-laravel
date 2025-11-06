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
        Schema::table('notification_reads', function (Blueprint $table) {
            // First, modify the notification_id column to match the new notifications table structure
            // The notifications table now uses an auto-incrementing bigint as notification_id
            $table->dropIndex(['notification_id']);
            $table->dropUnique(['notification_id', 'user_id']);
        });

        Schema::table('notification_reads', function (Blueprint $table) {
            // Add foreign key constraints
            $table->foreign('notification_id')->references('notification_id')->on('notifications')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // Recreate the unique constraint and indexes
            $table->unique(['notification_id', 'user_id']);
            $table->index(['notification_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_reads', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['notification_id']);
            $table->dropForeign(['user_id']);
            
            // Drop unique constraint and index
            $table->dropUnique(['notification_id', 'user_id']);
            $table->dropIndex(['notification_id']);
        });

        Schema::table('notification_reads', function (Blueprint $table) {
            // Recreate original constraints and indexes
            $table->unique(['notification_id', 'user_id']);
            $table->index(['notification_id']);
        });
    }
};
