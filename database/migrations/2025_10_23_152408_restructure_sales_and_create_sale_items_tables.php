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
        // Update the existing sale_items table structure
        Schema::table('sale_items', function (Blueprint $table) {
            // Add new columns we need
            if (!Schema::hasColumn('sale_items', 'size_id')) {
                $table->unsignedBigInteger('size_id')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('sale_items', 'product_name')) {
                $table->string('product_name')->after('size_id');
            }
            if (!Schema::hasColumn('sale_items', 'product_brand')) {
                $table->string('product_brand')->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('sale_items', 'product_size')) {
                $table->string('product_size')->nullable()->after('product_brand');
            }
            if (!Schema::hasColumn('sale_items', 'product_color')) {
                $table->string('product_color')->nullable()->after('product_size');
            }
            if (!Schema::hasColumn('sale_items', 'product_category')) {
                $table->string('product_category')->nullable()->after('product_color');
            }
            if (!Schema::hasColumn('sale_items', 'sku')) {
                $table->string('sku')->nullable()->after('product_category');
            }
            if (!Schema::hasColumn('sale_items', 'cost_price')) {
                $table->decimal('cost_price', 10, 2)->nullable()->after('unit_price');
            }
            
            // Rename total_price to subtotal
            if (Schema::hasColumn('sale_items', 'total_price')) {
                $table->renameColumn('total_price', 'subtotal');
            }
        });

        // Add indexes for performance (Laravel will skip if they exist)
        try {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->index(['sale_id', 'product_id'], 'sale_items_sale_id_product_id_index');
                $table->index('product_id', 'sale_items_product_id_index');
                $table->index('product_name', 'sale_items_product_name_index');
            });
        } catch (\Exception $e) {
            // Indexes might already exist, ignore
        }

        // Then modify the sales table - remove columns we don't need
        Schema::table('sales', function (Blueprint $table) {
            // Drop columns we're removing
            if (Schema::hasColumn('sales', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }
            if (Schema::hasColumn('sales', 'tax')) {
                $table->dropColumn('tax');
            }
            if (Schema::hasColumn('sales', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }
            if (Schema::hasColumn('sales', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('sales', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('sales', 'items')) {
                $table->dropColumn('items');
            }
            if (Schema::hasColumn('sales', 'total_items')) {
                $table->dropColumn('total_items');
            }
            if (Schema::hasColumn('sales', 'total_quantity')) {
                $table->dropColumn('total_quantity');
            }
        });

        // Rename columns to be more consistent
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'user_id')) {
                $table->renameColumn('user_id', 'cashier_id');
            }
            if (Schema::hasColumn('sales', 'total')) {
                $table->renameColumn('total', 'total_amount');
            }
            if (Schema::hasColumn('sales', 'change_amount')) {
                $table->renameColumn('change_amount', 'change_given');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse column renames first
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'cashier_id')) {
                $table->renameColumn('cashier_id', 'user_id');
            }
            if (Schema::hasColumn('sales', 'total_amount')) {
                $table->renameColumn('total_amount', 'total');
            }
            if (Schema::hasColumn('sales', 'change_given')) {
                $table->renameColumn('change_given', 'change_amount');
            }
        });

        // Add back the removed columns
        Schema::table('sales', function (Blueprint $table) {
            $table->string('receipt_number')->unique()->after('transaction_id');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
            $table->enum('payment_method', ['cash', 'gcash'])->default('cash')->after('change_given');
            $table->enum('status', ['completed', 'refunded', 'voided'])->default('completed')->after('payment_method');
            $table->json('items')->after('payment_method');
            $table->integer('total_items')->after('items');
            $table->integer('total_quantity')->after('total_items');
        });

        // Drop the sale_items table
        Schema::dropIfExists('sale_items');
    }
};
