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
        // Move users with 'manager' role from customers table to employees table
        $managerUsers = DB::table('users')->where('role', 'manager')->get();
        
        foreach ($managerUsers as $user) {
            // Check if they exist in customers table
            $customer = DB::table('customers')->where('user_id', $user->user_id)->first();
            
            if ($customer) {
                // Create employee record
                DB::table('employees')->insert([
                    'user_id' => $user->user_id,
                    'fullname' => $customer->fullname,
                    'email' => $customer->email,
                    'phone_number' => $customer->phone_number,
                    'hire_date' => $customer->created_at ? date('Y-m-d', strtotime($customer->created_at)) : date('Y-m-d'),
                    'position' => 'manager',
                    'profile_picture' => null,
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                ]);
                
                // Remove from customers table
                DB::table('customers')->where('user_id', $user->user_id)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Move users with 'manager' role back from employees table to customers table
        $managerUsers = DB::table('users')->where('role', 'manager')->get();
        
        foreach ($managerUsers as $user) {
            // Check if they exist in employees table
            $employee = DB::table('employees')->where('user_id', $user->user_id)->first();
            
            if ($employee) {
                // Create customer record
                DB::table('customers')->insert([
                    'user_id' => $user->user_id,
                    'fullname' => $employee->fullname,
                    'email' => $employee->email,
                    'phone_number' => $employee->phone_number,
                    'created_at' => $employee->created_at,
                    'updated_at' => $employee->updated_at,
                ]);
                
                // Remove from employees table
                DB::table('employees')->where('user_id', $user->user_id)->delete();
            }
        }
    }
};
