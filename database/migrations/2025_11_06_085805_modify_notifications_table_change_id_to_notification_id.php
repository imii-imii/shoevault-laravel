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
        Schema::table('notifications', function (Blueprint $table) {
            // Drop existing indexes that reference the 'id' column
            $table->dropIndex(['target_role', 'is_read', 'created_at']);
            $table->dropIndex(['type', 'created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Rename the 'id' column to 'notification_id'
            $table->renameColumn('id', 'notification_id');
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Recreate indexes
            $table->index(['target_role', 'is_read', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['target_role', 'is_read', 'created_at']);
            $table->dropIndex(['type', 'created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Rename the 'notification_id' column back to 'id'
            $table->renameColumn('notification_id', 'id');
        });

        Schema::table('notifications', function (Blueprint $table) {
            // Recreate original indexes
            $table->index(['target_role', 'is_read', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }
};
