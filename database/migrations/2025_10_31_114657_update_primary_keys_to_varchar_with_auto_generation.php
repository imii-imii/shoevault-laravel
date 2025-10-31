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
        // Step 1: Backup existing data
        $users = DB::table('users')->get();
        $employees = collect(); // Default to empty collection
        $customers = collect(); // Default to empty collection
        $transactions = collect(); // Default to empty collection
        $transactionItems = collect(); // Default to empty collection
        $supplyLogs = collect(); // Default to empty collection
        
        // Check if tables exist before querying them
        if (Schema::hasTable('employees')) {
            $employees = DB::table('employees')->get();
        }
        
        if (Schema::hasTable('customers')) {
            $customers = DB::table('customers')->get();
        }
        
        if (Schema::hasTable('transactions')) {
            $transactions = DB::table('transactions')->get();
        }
        
        if (Schema::hasTable('transaction_items')) {
            $transactionItems = DB::table('transaction_items')->get();
        }
        
        if (Schema::hasTable('supply_logs')) {
            $supplyLogs = DB::table('supply_logs')->get();
        }

        // Step 2: Drop tables to avoid foreign key constraint issues (in correct order)
        Schema::dropIfExists('supply_logs');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('users');

        // Step 3: Create users table with varchar primary key
        Schema::create('users', function (Blueprint $table) {
            $table->string('user_id', 20)->primary();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role')->default('employee');
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Step 4: Create employees table with varchar primary key
        Schema::create('employees', function (Blueprint $table) {
            $table->string('employee_id', 20)->primary();
            $table->string('user_id', 20);
            $table->string('fullname');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('position')->nullable();
            $table->string('profile_picture')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Step 5: Create customers table with varchar primary key
        Schema::create('customers', function (Blueprint $table) {
            $table->string('customer_id', 20)->primary();
            $table->string('user_id', 20);
            $table->string('fullname');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->string('email_verification_code')->nullable();
            $table->timestamp('email_verification_code_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Step 6: Recreate transactions table with updated foreign key
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

        // Step 7: Recreate transaction_items table
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('product_size_id');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->timestamps();
            
            $table->foreign('transaction_id')->references('transaction_id')->on('transactions')->onDelete('cascade');
            $table->foreign('product_size_id')->references('id')->on('product_sizes')->onDelete('cascade');
        });

        // Step 8: Recreate supply_logs table
        Schema::create('supply_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('product_size_id');
            $table->integer('quantity_supplied');
            $table->decimal('cost_per_unit', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->timestamp('supplied_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('product_size_id')->references('id')->on('product_sizes')->onDelete('cascade');
        });

        // Step 9: Generate new IDs and restore data
        $userIdMapping = [];
        foreach ($users as $oldUser) {
            $newUserId = 'USR' . strtoupper(substr(md5(uniqid() . $oldUser->user_id), 0, 12));
            $userIdMapping[$oldUser->user_id] = $newUserId;
            
            // Insert user with new ID
            DB::table('users')->insert([
                'user_id' => $newUserId,
                'username' => $oldUser->username,
                'password' => $oldUser->password,
                'role' => $oldUser->role,
                'is_active' => $oldUser->is_active,
                'email_verified_at' => $oldUser->email_verified_at,
                'remember_token' => $oldUser->remember_token,
                'created_at' => $oldUser->created_at,
                'updated_at' => $oldUser->updated_at,
            ]);
        }

        // Restore employees with new IDs (if any existed)
        $customerIdMapping = [];
        foreach ($employees as $relatedEmployee) {
            $newEmployeeId = 'EMP' . strtoupper(substr(md5(uniqid() . $relatedEmployee->employee_id), 0, 12));
            $newUserId = $userIdMapping[$relatedEmployee->user_id] ?? null;
            
            if ($newUserId) {
                DB::table('employees')->insert([
                    'employee_id' => $newEmployeeId,
                    'user_id' => $newUserId,
                    'fullname' => $relatedEmployee->fullname,
                    'email' => $relatedEmployee->email,
                    'phone_number' => $relatedEmployee->phone_number,
                    'hire_date' => $relatedEmployee->hire_date,
                    'position' => $relatedEmployee->position,
                    'profile_picture' => $relatedEmployee->profile_picture,
                    'created_at' => $relatedEmployee->created_at,
                    'updated_at' => $relatedEmployee->updated_at,
                ]);
            }
        }

        // Restore customers with new IDs (if any existed)
        foreach ($customers as $relatedCustomer) {
            $newCustomerId = 'CUST' . strtoupper(substr(md5(uniqid() . $relatedCustomer->customer_id), 0, 11));
            $newUserId = $userIdMapping[$relatedCustomer->user_id] ?? null;
            $customerIdMapping[$relatedCustomer->customer_id] = $newCustomerId;
            
            if ($newUserId) {
                DB::table('customers')->insert([
                    'customer_id' => $newCustomerId,
                    'user_id' => $newUserId,
                    'fullname' => $relatedCustomer->fullname,
                    'email' => $relatedCustomer->email,
                    'phone_number' => $relatedCustomer->phone_number,
                    'email_verification_code' => $relatedCustomer->email_verification_code ?? null,
                    'email_verification_code_expires_at' => $relatedCustomer->email_verification_code_expires_at ?? null,
                    'email_verified_at' => $relatedCustomer->email_verified_at ?? null,
                    'created_at' => $relatedCustomer->created_at,
                    'updated_at' => $relatedCustomer->updated_at,
                ]);
            }
        }

        // Restore transactions with new IDs (if any existed)
        $transactionIdMapping = [];
        foreach ($transactions as $transaction) {
            $newCashierId = $userIdMapping[$transaction->cashier_id] ?? null;
            $newCustomerId = $customerIdMapping[$transaction->customer_id] ?? null;
            
            $insertedId = DB::table('transactions')->insertGetId([
                'cashier_id' => $newCashierId,
                'customer_id' => $newCustomerId,
                'total_amount' => $transaction->total_amount,
                'payment_method' => $transaction->payment_method,
                'transaction_date' => $transaction->transaction_date,
                'transaction_type' => $transaction->transaction_type,
                'notes' => $transaction->notes,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ]);
            
            $transactionIdMapping[$transaction->transaction_id] = $insertedId;
        }

        // Restore transaction items (if any existed)
        foreach ($transactionItems as $item) {
            $newTransactionId = $transactionIdMapping[$item->transaction_id] ?? null;
            
            if ($newTransactionId) {
                DB::table('transaction_items')->insert([
                    'transaction_id' => $newTransactionId,
                    'product_size_id' => $item->product_size_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);
            }
        }

        // Restore supply logs (if any existed)
        foreach ($supplyLogs as $log) {
            DB::table('supply_logs')->insert([
                'supplier_id' => $log->supplier_id,
                'product_size_id' => $log->product_size_id,
                'quantity_supplied' => $log->quantity_supplied,
                'cost_per_unit' => $log->cost_per_unit,
                'total_cost' => $log->total_cost,
                'supplied_at' => $log->supplied_at,
                'notes' => $log->notes,
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ]);
        }
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id']);
        });

        // Revert users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->id('user_id')->first();
        });

        // Revert customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->dropPrimary(['customer_id']);
            $table->dropColumn(['customer_id', 'user_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->id('customer_id')->first();
            $table->unsignedBigInteger('user_id')->after('customer_id');
        });

        // Revert employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->dropPrimary(['employee_id']);
            $table->dropColumn(['employee_id', 'user_id']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->id('employee_id')->first();
            $table->unsignedBigInteger('user_id')->after('employee_id');
        });

        // Revert reservations table
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->after('reservation_id');
        });

        // Re-add foreign key constraints
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->index('customer_id');
        });
    }
};
