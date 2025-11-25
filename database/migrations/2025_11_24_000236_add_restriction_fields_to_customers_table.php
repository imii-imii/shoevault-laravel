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
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_restricted')->default(false)->after('email_verified_at');
            $table->timestamp('restricted_until')->nullable()->after('is_restricted');
            $table->text('restriction_reason')->nullable()->after('restricted_until');
            $table->string('restricted_by')->nullable()->after('restriction_reason'); // Who applied the restriction
            $table->timestamp('restricted_at')->nullable()->after('restricted_by'); // When restriction was applied
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['is_restricted', 'restricted_until', 'restriction_reason', 'restricted_by', 'restricted_at']);
        });
    }
};
