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
        Schema::table('suppliers', function (Blueprint $table) {
            // Add country if missing
            if (!Schema::hasColumn('suppliers', 'country')) {
                $table->string('country')->nullable()->after('contact_person');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            // Drop columns we no longer use if they exist
            if (Schema::hasColumn('suppliers', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('suppliers', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('suppliers', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('suppliers', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Recreate removed columns (as nullable to avoid data issues)
            if (!Schema::hasColumn('suppliers', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('suppliers', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('suppliers', 'notes')) {
                $table->text('notes')->nullable()->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('notes');
            }
        });

        // Drop country if present (we can't know original order, so just drop if exists)
        if (Schema::hasColumn('suppliers', 'country')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('country');
            });
        }
    }
};
