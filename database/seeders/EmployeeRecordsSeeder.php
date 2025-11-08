<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;

class EmployeeRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users that don't have employee records yet
        $users = User::whereDoesntHave('employee')->get();
        
        foreach ($users as $user) {
            // Create employee record based on user role
            $employeeData = [
                'user_id' => $user->user_id,
                'position' => $user->role,
                'hire_date' => now(),
            ];
            
            // Set default data based on role
            switch ($user->role) {
                case 'owner':
                    $employeeData['fullname'] = 'ShoeVault Owner';
                    $employeeData['email'] = 'owner@shoevault.com';
                    $employeeData['phone_number'] = '+63 917 123 4567';
                    break;
                case 'manager':
                    $employeeData['fullname'] = 'Store Manager';
                    $employeeData['email'] = 'manager@shoevault.com';
                    $employeeData['phone_number'] = '+63 917 234 5678';
                    break;
                case 'cashier':
                    $employeeData['fullname'] = 'Store Cashier';
                    $employeeData['email'] = 'cashier@shoevault.com';
                    $employeeData['phone_number'] = '+63 917 345 6789';
                    break;
                default:
                    $employeeData['fullname'] = ucfirst($user->role) . ' User';
                    $employeeData['email'] = strtolower($user->role) . '@shoevault.com';
                    $employeeData['phone_number'] = '+63 917 000 0000';
                    break;
            }
            
            Employee::create($employeeData);
            echo "Created employee record for user: {$user->username}\n";
        }
        
        echo "Employee records seeding completed!\n";
    }
}
