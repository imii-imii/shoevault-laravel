<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Additional columns to match UI fields
            if (!Schema::hasColumn('suppliers', 'brands')) {
                $table->json('brands')->nullable()->after('contact_person');
            }
            if (!Schema::hasColumn('suppliers', 'total_stock')) {
                $table->unsignedInteger('total_stock')->default(0)->after('brands');
            }
            if (!Schema::hasColumn('suppliers', 'country')) {
                $table->string('country')->nullable()->after('total_stock');
            }
            if (!Schema::hasColumn('suppliers', 'available_sizes')) {
                $table->string('available_sizes')->nullable()->after('country');
            }
            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->string('status')->default('active')->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'brands')) {
                $table->dropColumn('brands');
            }
            if (Schema::hasColumn('suppliers', 'total_stock')) {
                $table->dropColumn('total_stock');
            }
            if (Schema::hasColumn('suppliers', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('suppliers', 'available_sizes')) {
                $table->dropColumn('available_sizes');
            }
            if (Schema::hasColumn('suppliers', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
