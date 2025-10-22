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
        Schema::table('sales', function (Blueprint $table) {
            // Add new columns for better sales tracking
            if (!Schema::hasColumn('sales', 'receipt_number')) {
                $table->string('receipt_number')->unique()->after('transaction_id');
            }
            if (!Schema::hasColumn('sales', 'sale_type')) {
                $table->enum('sale_type', ['pos', 'reservation'])->default('pos')->after('receipt_number');
            }
            if (!Schema::hasColumn('sales', 'reservation_id')) {
                $table->string('reservation_id')->nullable()->after('sale_type');
            }
            
            // Add discount column
            if (!Schema::hasColumn('sales', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('tax');
            }
            
            // Add tracking columns
            if (!Schema::hasColumn('sales', 'total_items')) {
                $table->integer('total_items')->after('items'); // Count of different products
            }
            if (!Schema::hasColumn('sales', 'total_quantity')) {
                $table->integer('total_quantity')->after('total_items'); // Sum of all quantities
            }
            
            // Improve status tracking
            if (!Schema::hasColumn('sales', 'status')) {
                $table->enum('status', ['completed', 'refunded', 'voided'])->default('completed')->after('notes');
            }
            if (!Schema::hasColumn('sales', 'sale_date')) {
                $table->timestamp('sale_date')->after('status'); // Separate from created_at for reporting
            }
        });
        
        // Add indexes for better performance
        try {
            Schema::table('sales', function (Blueprint $table) {
                $table->index(['sale_date', 'sale_type'], 'idx_sales_date_type');
                $table->index(['user_id', 'sale_date'], 'idx_sales_user_date');
                $table->index(['status', 'sale_date'], 'idx_sales_status_date');
                $table->index('total', 'idx_sales_total');
            });
        } catch (\Exception $e) {
            // Indexes may already exist, ignore errors
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop added columns if they exist
            if (Schema::hasColumn('sales', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }
            if (Schema::hasColumn('sales', 'sale_type')) {
                $table->dropColumn('sale_type');
            }
            if (Schema::hasColumn('sales', 'reservation_id')) {
                $table->dropColumn('reservation_id');
            }
            if (Schema::hasColumn('sales', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
            if (Schema::hasColumn('sales', 'total_items')) {
                $table->dropColumn('total_items');
            }
            if (Schema::hasColumn('sales', 'total_quantity')) {
                $table->dropColumn('total_quantity');
            }
            if (Schema::hasColumn('sales', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('sales', 'sale_date')) {
                $table->dropColumn('sale_date');
            }
        });
    }
};
