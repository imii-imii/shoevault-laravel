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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, datetime, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default operating hours settings
        DB::table('system_settings')->insert([
            [
                'key' => 'operating_hours_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable operating hours restriction for manager and cashier users',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'operating_hours_start',
                'value' => '10:00',
                'type' => 'string',
                'description' => 'Store opening time (24-hour format)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'operating_hours_end',
                'value' => '19:00',
                'type' => 'string',
                'description' => 'Store closing time (24-hour format)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'emergency_access_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Allow emergency access outside operating hours',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'emergency_access_expires_at',
                'value' => null,
                'type' => 'datetime',
                'description' => 'When emergency access expires',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'emergency_access_duration',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Emergency access duration in minutes (configurable by owner)',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
