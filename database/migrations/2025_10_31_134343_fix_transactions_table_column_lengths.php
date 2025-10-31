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
        Schema::table('transactions', function (Blueprint $table) {
            // Increase the length of varchar columns to accommodate the auto-generated IDs
            $table->string('transaction_id', 25)->change(); // Increase from 20 to 25
            $table->string('reservation_id', 25)->nullable()->change(); // Increase from 20 to 25  
            $table->string('user_id', 25)->change(); // Increase from 20 to 25
        });
        
        Schema::table('transaction_items', function (Blueprint $table) {
            // Also fix the transaction_id column in transaction_items
            $table->string('transaction_id', 25)->change(); // Increase from 20 to 25
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Revert back to original lengths
            $table->string('transaction_id', 20)->change();
            $table->string('reservation_id', 20)->nullable()->change();
            $table->string('user_id', 20)->change();
        });
        
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->string('transaction_id', 20)->change();
        });
    }
};
