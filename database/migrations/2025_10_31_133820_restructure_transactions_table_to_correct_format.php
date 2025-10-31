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
        // First, drop foreign key constraints from transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
        });
        
        // Drop the existing transactions table
        Schema::dropIfExists('transactions');
        
        // Create the new transactions table with correct structure
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('transaction_id', 20)->primary(); // PRIMARY KEY
            $table->enum('sale_type', ['pos', 'reservation']); // Type of sale
            $table->string('reservation_id', 20)->nullable(); // For reservation sales
            $table->string('user_id', 20); // WHICH STAFF MADE THE TRANSACTION
            $table->decimal('subtotal', 10, 2); // Subtotal before discount
            $table->decimal('discount_amount', 10, 2)->default(0); // Discount applied
            $table->decimal('total_amount', 10, 2); // Final total amount
            $table->decimal('amount_paid', 10, 2); // Amount customer paid
            $table->decimal('change_given', 10, 2)->default(0); // Change given to customer
            $table->timestamp('sale_date')->useCurrent(); // When the sale happened
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('set null');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
        
        // Update transaction_items table to reference the new transaction_id structure
        Schema::table('transaction_items', function (Blueprint $table) {
            // Change transaction_id column to string to match new structure
            $table->dropColumn('transaction_id');
        });
        
        Schema::table('transaction_items', function (Blueprint $table) {
            // Add new transaction_id column as string
            $table->string('transaction_id', 20)->after('id');
            
            // Add foreign key constraint
            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new transactions table
        Schema::dropIfExists('transactions');
        
        // Recreate the old transactions table structure
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->string('cashier_id', 20)->nullable();
            $table->string('customer_id', 20)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'gcash', 'maya']);
            $table->timestamp('transaction_date')->useCurrent();
            $table->enum('transaction_type', ['sale', 'return', 'exchange'])->default('sale');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('cashier_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('customer_id')->references('customer_id')->on('customers')->onDelete('set null');
        });
        
        // Restore transaction_items table structure
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');
        });
        
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_id')->after('id');
            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
        });
    }
};
