<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Owner account
        $ownerUser = User::create([
            'username' => 'owner',
            'password' => Hash::make('owner123'),
            'role' => 'owner',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create corresponding Employee record for Owner
        Employee::create([
            'user_id' => $ownerUser->user_id,
            'fullname' => 'ShoeVault Owner',
            'email' => 'owner@shoevault.com',
            'phone_number' => '+63 917 123 4567',
            'position' => 'owner',
            'hire_date' => now(),
        ]);

        // Create Manager account
        $managerUser = User::create([
            'username' => 'manager',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create corresponding Employee record for Manager
        Employee::create([
            'user_id' => $managerUser->user_id,
            'fullname' => 'Store Manager',
            'email' => 'manager@shoevault.com',
            'phone_number' => '+63 917 234 5678',
            'position' => 'manager',
            'hire_date' => now(),
        ]);

        // Create Cashier account
        $cashierUser = User::create([
            'username' => 'cashier',
            'password' => Hash::make('cashier123'),
            'role' => 'cashier',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create corresponding Employee record for Cashier
        Employee::create([
            'user_id' => $cashierUser->user_id,
            'fullname' => 'Store Cashier',
            'email' => 'cashier@shoevault.com',
            'phone_number' => '+63 917 345 6789',
            'position' => 'cashier',
            'hire_date' => now(),
        ]);

        echo "Default users created successfully!\n";
    }
}