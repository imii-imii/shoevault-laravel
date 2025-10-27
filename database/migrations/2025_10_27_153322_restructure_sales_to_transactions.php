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
        // Step 1: Temporarily disable foreign key checks to avoid constraint issues
        Schema::disableForeignKeyConstraints();

        // Step 2: Create new 'transactions' table (renamed from 'sales')
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('transaction_id')->primary(); // transaction_id as primary key
            $table->enum('sale_type', ['pos', 'reservation']);
            $table->string('reservation_id')->nullable();
            $table->string('cashier_id')->nullable(); // Make nullable to handle null values
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('change_given', 10, 2)->default(0.00);
            $table->date('sale_date');
            $table->timestamps();
        });

        // Step 3: Create new 'transaction_items' table (renamed from 'sale_items')
        Schema::dropIfExists('transaction_items');
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id'); // renamed from sale_id
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('size_id')->nullable();
            $table->string('product_name');
            $table->string('product_brand');
            $table->string('product_size');
            $table->string('product_color');
            $table->string('product_category');
            $table->string('sku');
            $table->integer('quantity');
            $table->string('size');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('cost_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            // Add foreign key constraint to transactions table
            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
        });

        // Step 4: Migrate data from 'sales' to 'transactions'
        if (Schema::hasTable('sales')) {
            $sales = DB::table('sales')->get();
            foreach ($sales as $sale) {
                DB::table('transactions')->insert([
                    'transaction_id' => $sale->transaction_id,
                    'sale_type' => $sale->sale_type,
                    'reservation_id' => $sale->reservation_id,
                    'cashier_id' => $sale->cashier_id,
                    'subtotal' => $sale->subtotal,
                    'discount_amount' => $sale->discount_amount,
                    'total_amount' => $sale->total_amount,
                    'amount_paid' => $sale->amount_paid,
                    'change_given' => $sale->change_given,
                    'sale_date' => $sale->sale_date,
                    'created_at' => $sale->created_at,
                    'updated_at' => $sale->updated_at,
                ]);
            }
        }

        // Step 5: Migrate data from 'sale_items' to 'transaction_items'
        if (Schema::hasTable('sale_items')) {
            $saleItems = DB::table('sale_items')->get();
            foreach ($saleItems as $item) {
                // Get the transaction_id from the sales table using the sale_id
                $transactionId = DB::table('sales')->where('id', $item->sale_id)->value('transaction_id');
                
                if ($transactionId) {
                    DB::table('transaction_items')->insert([
                        'transaction_id' => $transactionId,
                        'product_id' => $item->product_id,
                        'size_id' => $item->size_id,
                        'product_name' => $item->product_name,
                        'product_brand' => $item->product_brand,
                        'product_size' => $item->product_size,
                        'product_color' => $item->product_color,
                        'product_category' => $item->product_category,
                        'sku' => $item->sku,
                        'quantity' => $item->quantity,
                        'size' => $item->size,
                        'unit_price' => $item->unit_price,
                        'cost_price' => $item->cost_price,
                        'subtotal' => $item->subtotal,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ]);
                }
            }
        }

        // Step 6: Drop old tables
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');

        // Step 7: Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Temporarily disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Step 2: Create old 'sales' table
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->enum('sale_type', ['pos', 'reservation']);
            $table->string('reservation_id')->nullable();
            $table->string('cashier_id')->nullable(); // Make nullable
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('change_given', 10, 2)->default(0.00);
            $table->date('sale_date');
            $table->timestamps();
        });

        // Step 3: Create old 'sale_items' table
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('size_id')->nullable();
            $table->string('product_name');
            $table->string('product_brand');
            $table->string('product_size');
            $table->string('product_color');
            $table->string('product_category');
            $table->string('sku');
            $table->integer('quantity');
            $table->string('size');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('cost_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });

        // Step 4: Migrate data back from 'transactions' to 'sales'
        if (Schema::hasTable('transactions')) {
            $transactions = DB::table('transactions')->get();
            foreach ($transactions as $transaction) {
                $saleId = DB::table('sales')->insertGetId([
                    'transaction_id' => $transaction->transaction_id,
                    'sale_type' => $transaction->sale_type,
                    'reservation_id' => $transaction->reservation_id,
                    'cashier_id' => $transaction->cashier_id,
                    'subtotal' => $transaction->subtotal,
                    'discount_amount' => $transaction->discount_amount,
                    'total_amount' => $transaction->total_amount,
                    'amount_paid' => $transaction->amount_paid,
                    'change_given' => $transaction->change_given,
                    'sale_date' => $transaction->sale_date,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ]);

                // Migrate transaction_items back to sale_items
                $transactionItems = DB::table('transaction_items')
                    ->where('transaction_id', $transaction->transaction_id)
                    ->get();

                foreach ($transactionItems as $item) {
                    DB::table('sale_items')->insert([
                        'sale_id' => $saleId,
                        'product_id' => $item->product_id,
                        'size_id' => $item->size_id,
                        'product_name' => $item->product_name,
                        'product_brand' => $item->product_brand,
                        'product_size' => $item->product_size,
                        'product_color' => $item->product_color,
                        'product_category' => $item->product_category,
                        'sku' => $item->sku,
                        'quantity' => $item->quantity,
                        'size' => $item->size,
                        'unit_price' => $item->unit_price,
                        'cost_price' => $item->cost_price,
                        'subtotal' => $item->subtotal,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ]);
                }
            }
        }

        // Step 5: Drop new tables
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');

        // Step 6: Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
};
