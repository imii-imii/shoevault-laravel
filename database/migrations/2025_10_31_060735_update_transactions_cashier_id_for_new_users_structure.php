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
        // The cashier_id in transactions table was previously referencing users.id (old structure)
        // Now it needs to reference users.user_id (new structure)
        // We need to map old user IDs to new user_id values based on username matching
        
        // First, let's create a mapping of old user data to new user data
        $transactions = DB::table('transactions')->whereNotNull('cashier_id')->get();
        
        foreach ($transactions as $transaction) {
            // For now, since we don't have a clear mapping, we'll try to find users by the cashier_id
            // This assumes the cashier_id was the old user.id and we need to find the corresponding new user
            
            // Get the old user data from backup if available, or try to match by existing user
            $newUser = DB::table('users')
                ->join('employees', 'users.user_id', '=', 'employees.user_id')
                ->where('employees.position', 'like', '%cashier%')
                ->orWhere('employees.position', 'like', '%admin%')
                ->orWhere('employees.position', 'like', '%owner%')
                ->first();
            
            if ($newUser) {
                // Update the cashier_id to the new user_id
                DB::table('transactions')
                    ->where('transaction_id', $transaction->transaction_id)
                    ->update(['cashier_id' => $newUser->user_id]);
            } else {
                // If no matching user found, set to null
                DB::table('transactions')
                    ->where('transaction_id', $transaction->transaction_id)
                    ->update(['cashier_id' => null]);
            }
        }
        
        // Now modify the cashier_id column to be unsignedBigInteger to match user_id type
        Schema::table('transactions', function (Blueprint $table) {
            // Change cashier_id to unsignedBigInteger
            $table->unsignedBigInteger('cashier_id')->nullable()->change();
            
            // Add foreign key constraint
            $table->foreign('cashier_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            try {
                // Drop foreign key constraint
                $table->dropForeign(['cashier_id']);
                
                // Change cashier_id back to string
                $table->string('cashier_id')->nullable()->change();
            } catch (\Exception $e) {
                // Handle gracefully if constraints don't exist
            }
        });
    }
};
