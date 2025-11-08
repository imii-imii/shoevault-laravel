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
        // This migration is no longer needed since products table now uses product_id as primary key from the beginning
        // All foreign key relationships are already correctly established in the original migrations
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is no longer needed since products table now uses product_id as primary key from the beginning
        // All foreign key relationships are already correctly established in the original migrations
    }
};
