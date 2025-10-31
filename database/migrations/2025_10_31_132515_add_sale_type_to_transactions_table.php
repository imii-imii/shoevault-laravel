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
            // Add sale_type column if it doesn't exist
            if (!Schema::hasColumn('transactions', 'sale_type')) {
                $table->enum('sale_type', ['pos', 'reservation'])->after('transaction_id');
            }
            
            // Add reservation_id column if it doesn't exist
            if (!Schema::hasColumn('transactions', 'reservation_id')) {
                $table->string('reservation_id')->nullable()->after('sale_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'reservation_id')) {
                $table->dropColumn('reservation_id');
            }
            if (Schema::hasColumn('transactions', 'sale_type')) {
                $table->dropColumn('sale_type');
            }
        });
    }
};
