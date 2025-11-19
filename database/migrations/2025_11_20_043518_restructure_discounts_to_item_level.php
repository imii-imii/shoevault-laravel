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
        // First, add discount fields to transaction_items table
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0)->after('cost_price'); // EXACT currency amount discounted from this item
            $table->decimal('subtotal', 10, 2)->after('discount_amount'); // Subtotal for this item (quantity * unit_price - discount_amount)
        });

        // Calculate subtotals for existing transaction items (no discounts applied to existing data)
        $transactionItems = DB::table('transaction_items')->get();
        foreach ($transactionItems as $item) {
            $subtotal = $item->quantity * $item->unit_price; // No discount for existing items
            DB::table('transaction_items')
                ->where('id', $item->id)
                ->update([
                    'discount_amount' => 0, // No discount amount for existing items
                    'subtotal' => $subtotal
                ]);
        }

        // Remove obsolete discount fields from transactions table since discounts are now item-level
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'subtotal']); // Remove transaction-level discount fields
        });

        // Recalculate total_amount for existing transactions based on item subtotals
        $transactions = DB::table('transactions')->get();
        foreach ($transactions as $transaction) {
            $itemsTotal = DB::table('transaction_items')
                ->where('transaction_id', $transaction->transaction_id)
                ->sum('subtotal'); // Sum of all item subtotals (which currently have no discounts)
            
            DB::table('transactions')
                ->where('transaction_id', $transaction->transaction_id)
                ->update(['total_amount' => $itemsTotal]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the removed fields to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->after('user_id');
            $table->decimal('discount_amount', 10, 2)->after('subtotal');
        });

        // Remove discount fields from transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'subtotal']);
        });
    }
};
