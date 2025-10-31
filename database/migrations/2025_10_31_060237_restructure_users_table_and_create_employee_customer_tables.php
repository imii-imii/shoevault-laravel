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
        // Drop any existing backup table first
        Schema::dropIfExists('users_backup');
        
        // First, let's backup existing user data
        $existingUsers = DB::table('users')->get();

        // Create temporary table to store user data during transition
        Schema::create('users_backup', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('employee');
            $table->json('permissions')->nullable();
            $table->string('phone')->nullable();
            $table->string('profile_picture')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // Copy existing data to backup table
        foreach ($existingUsers as $user) {
            DB::table('users_backup')->insert([
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'role' => $user->role ?? 'employee',
                'permissions' => $user->permissions,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture,
                'is_active' => $user->is_active ?? true,
                'remember_token' => $user->remember_token,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }

        // Drop the existing users table
        Schema::dropIfExists('users');

        // Create the new users table with user_id as primary key
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id'); // This creates user_id as PRIMARY KEY
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role')->default('employee');
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Create employees table
        Schema::create('employees', function (Blueprint $table) {
            $table->id('employee_id');
            $table->unsignedBigInteger('user_id');
            $table->string('fullname');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('position')->nullable();
            $table->string('profile_picture')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Create customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id');
            $table->unsignedBigInteger('user_id');
            $table->string('fullname');
            $table->string('email')->unique();
            $table->string('phone_number')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Migrate existing user data to new structure
        foreach ($existingUsers as $oldUser) {
            // Create new user record
            $newUserId = DB::table('users')->insertGetId([
                'username' => $oldUser->username,
                'password' => $oldUser->password,
                'role' => $oldUser->role ?? 'employee',
                'is_active' => $oldUser->is_active ?? true,
                'email_verified_at' => $oldUser->email_verified_at,
                'remember_token' => $oldUser->remember_token,
                'created_at' => $oldUser->created_at,
                'updated_at' => $oldUser->updated_at,
            ]);

            // Create employee or customer record based on role
            if (in_array($oldUser->role ?? 'employee', ['owner', 'admin', 'employee', 'staff', 'cashier'])) {
                DB::table('employees')->insert([
                    'user_id' => $newUserId,
                    'fullname' => $oldUser->name,
                    'email' => $oldUser->email,
                    'phone_number' => $oldUser->phone ?? null,
                    'hire_date' => $oldUser->created_at ? date('Y-m-d', strtotime($oldUser->created_at)) : date('Y-m-d'),
                    'position' => $oldUser->role ?? 'employee',
                    'profile_picture' => $oldUser->profile_picture ?? null,
                    'created_at' => $oldUser->created_at,
                    'updated_at' => $oldUser->updated_at,
                ]);
            } else {
                // Treat as customer
                DB::table('customers')->insert([
                    'user_id' => $newUserId,
                    'fullname' => $oldUser->name,
                    'email' => $oldUser->email,
                    'phone_number' => $oldUser->phone ?? null,
                    'created_at' => $oldUser->created_at,
                    'updated_at' => $oldUser->updated_at,
                ]);
            }
        }

        // Drop the backup table
        Schema::dropIfExists('users_backup');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get current data for rollback
        $users = DB::table('users')->get();
        $employees = DB::table('employees')->get();
        $customers = DB::table('customers')->get();

        // Drop new tables
        Schema::dropIfExists('customers');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('users');

        // Recreate original users table structure
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('employee');
            $table->string('phone')->nullable();
            $table->string('profile_picture')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // Restore user data by combining users with employees/customers
        foreach ($users as $user) {
            $employee = $employees->firstWhere('user_id', $user->user_id);
            $customer = $customers->firstWhere('user_id', $user->user_id);
            
            $profile = $employee ?? $customer;
            
            DB::table('users')->insert([
                'name' => $profile->fullname ?? 'Unknown',
                'username' => $user->username,
                'email' => $profile->email ?? 'unknown@example.com',
                'email_verified_at' => $user->email_verified_at,
                'password' => $user->password,
                'role' => $user->role,
                'phone' => $profile->phone_number ?? null,
                'profile_picture' => $profile->profile_picture ?? null,
                'is_active' => $user->is_active,
                'remember_token' => $user->remember_token,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);
        }
    }
};
