<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SystemUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@shoevault.com',
            'password' => 'admin123',
            'role' => 'admin',
            'permissions' => ['all'],
            'is_active' => true,
        ]);

        // Cashier user
        User::create([
            'name' => 'Lauren Smith',
            'username' => 'cashier',
            'email' => 'cashier@shoevault.com',
            'password' => 'cashier123',
            'role' => 'cashier',
            'permissions' => ['pos', 'reports'],
            'is_active' => true,
        ]);

        // Manager user
        User::create([
            'name' => 'John Manager',
            'username' => 'manager',
            'email' => 'manager@shoevault.com',
            'password' => 'manager123',
            'role' => 'manager',
            'permissions' => ['inventory', 'suppliers', 'reports', 'settings'],
            'is_active' => true,
        ]);

        // Owner user (for future analytics dashboard)
        User::create([
            'name' => 'Business Owner',
            'username' => 'owner',
            'email' => 'owner@shoevault.com',
            'password' => 'owner123',
            'role' => 'owner',
            'permissions' => ['all'],
            'is_active' => true,
        ]);
    }
}
