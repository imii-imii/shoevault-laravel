<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                if (Schema::hasColumn('suppliers', 'phone')) {
                    $table->dropColumn('phone');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $table) {
                // Recreate the phone column as nullable string
                if (!Schema::hasColumn('suppliers', 'phone')) {
                    $table->string('phone')->nullable();
                }
            });
        }
    }
};
