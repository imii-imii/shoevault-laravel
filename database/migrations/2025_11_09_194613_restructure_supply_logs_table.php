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
        Schema::table('supply_logs', function (Blueprint $table) {
            // Check if foreign key exists before dropping
            try {
                $table->dropForeign(['product_size_id']);
            } catch (Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Check and drop columns if they exist
            if (Schema::hasColumn('supply_logs', 'product_size_id')) {
                $table->dropColumn('product_size_id');
            }
            if (Schema::hasColumn('supply_logs', 'cost_per_unit')) {
                $table->dropColumn('cost_per_unit');
            }
            if (Schema::hasColumn('supply_logs', 'total_cost')) {
                $table->dropColumn('total_cost');
            }
            if (Schema::hasColumn('supply_logs', 'notes')) {
                $table->dropColumn('notes');
            }
            
            // Rename columns if they exist and target doesn't exist
            if (Schema::hasColumn('supply_logs', 'quantity_supplied') && !Schema::hasColumn('supply_logs', 'quantity')) {
                $table->renameColumn('quantity_supplied', 'quantity');
            }
            if (Schema::hasColumn('supply_logs', 'supplied_at') && !Schema::hasColumn('supply_logs', 'received_at')) {
                $table->renameColumn('supplied_at', 'received_at');
            }
            
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('supply_logs', 'brand')) {
                $table->string('brand')->nullable()->after('supplier_id');
            }
            if (!Schema::hasColumn('supply_logs', 'size')) {
                $table->string('size')->nullable()->after('brand');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supply_logs', function (Blueprint $table) {
            // Remove the added columns
            $table->dropColumn(['brand', 'size']);
            
            // Rename columns back
            $table->renameColumn('quantity', 'quantity_supplied');
            $table->renameColumn('received_at', 'supplied_at');
            
            // Add back the removed columns
            $table->unsignedBigInteger('product_size_id')->after('supplier_id');
            $table->decimal('cost_per_unit', 10, 2)->after('quantity_supplied');
            $table->decimal('total_cost', 10, 2)->after('cost_per_unit');
            $table->text('notes')->nullable()->after('supplied_at');
            
            // Add back the foreign key
            $table->foreign('product_size_id')->references('id')->on('product_sizes')->onDelete('cascade');
        });
    }
};
