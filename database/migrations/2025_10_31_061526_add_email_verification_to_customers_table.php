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
            $table->string('email_verification_code', 6)->nullable()->after('email');
            $table->timestamp('email_verification_code_expires_at')->nullable()->after('email_verification_code');
            $table->timestamp('email_verified_at')->nullable()->after('email_verification_code_expires_at');
            $table->string('google_id')->nullable()->after('email_verified_at');
            $table->string('avatar')->nullable()->after('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_code',
                'email_verification_code_expires_at',
                'email_verified_at',
                'google_id',
                'avatar'
            ]);
        });
    }
};
