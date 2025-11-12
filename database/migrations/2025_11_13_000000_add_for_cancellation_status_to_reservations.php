<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the new status to the enum
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'completed', 'cancelled', 'for_cancellation') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any 'for_cancellation' statuses to 'pending' to avoid constraint violations
        DB::table('reservations')
            ->where('status', 'for_cancellation')
            ->update(['status' => 'pending']);
            
        // Remove the new status from the enum
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};