<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OwnerUsersController extends Controller
{
    // GET /owner/users
    public function index(Request $request)
    {
        $this->authorizeOwner();

        $q = trim((string) $request->get('search', ''));
        
        // Get employees with their user data
        $employeeQuery = Employee::with(['user' => function($query) {
            $query->select('user_id', 'username', 'role', 'is_active', 'created_at');
        }])->select(['employee_id', 'fullname', 'email', 'phone_number', 'user_id', 'created_at']);
        
        if ($q !== '') {
            $employeeQuery->where(function($w) use ($q){
                $w->where('fullname','like',"%$q%")
                  ->orWhere('email','like',"%$q%")
                  ->orWhereHas('user', function($userQuery) use ($q) {
                      $userQuery->where('username', 'like', "%$q%")
                                ->orWhere('role', 'like', "%$q%");
                  });
            });
        }
        
        $employeeData = $employeeQuery->orderBy('fullname')->get();
        
        // Also get users with employee roles that don't have employee records
        $userQuery = User::select(['user_id', 'username', 'role', 'is_active', 'created_at'])
            ->whereIn('role', ['manager', 'cashier', 'employee'])
            ->whereNotIn('user_id', $employeeData->pluck('user_id'));
            
        if ($q !== '') {
            $userQuery->where(function($w) use ($q){
                $w->where('username','like',"%$q%")
                  ->orWhere('role','like',"%$q%");
            });
        }
        
        $userData = $userQuery->orderBy('username')->get();
        
        // Combine both employee records and user-only records
        $users = collect();
        
        // Add employees with full records
        $employeeData->each(function ($employee) use ($users) {
            $users->push([
                'id' => $employee->employee_id,
                'name' => $employee->fullname,
                'username' => $employee->user->username ?? '',
                'email' => $employee->email,
                'phone' => $employee->phone_number,
                'role' => $employee->user->role ?? 'employee',
                'is_active' => $employee->user->is_active ?? true,
                'created_at' => $employee->user->created_at ?? $employee->created_at,
            ]);
        });
        
        // Add users without employee records
        $userData->each(function ($user) use ($users) {
            $users->push([
                'id' => $user->user_id,
                'name' => $user->username, // Fallback to username
                'username' => $user->username,
                'email' => 'Not set',
                'phone' => 'Not set',  
                'role' => $user->role,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
            ]);
        });

        return response()->json([ 'success' => true, 'users' => $users->toArray() ]);
    }

    // GET /owner/customers
    public function customers(Request $request)
    {
        $this->authorizeOwner();

        $q = trim((string) $request->get('search', ''));
        
        // Get customers with their user data
        $customerQuery = Customer::with(['user' => function($query) {
            $query->select('user_id', 'username', 'role', 'is_active', 'created_at');
        }])->select(['customer_id', 'fullname', 'email', 'phone_number', 'user_id', 'created_at']);
        
        if ($q !== '') {
            $customerQuery->where(function($w) use ($q){
                $w->where('fullname','like',"%$q%")
                  ->orWhere('email','like',"%$q%")
                  ->orWhereHas('user', function($userQuery) use ($q) {
                      $userQuery->where('username', 'like', "%$q%");
                  });
            });
        }
        
        $customerData = $customerQuery->orderBy('fullname')->get();
        
        // Transform customer data to match expected format
        $customers = $customerData->map(function ($customer) {
            return [
                'id' => $customer->customer_id,
                'name' => $customer->fullname,
                'username' => $customer->user->username ?? '',
                'email' => $customer->email,
                'phone' => $customer->phone_number,
                'status' => $customer->user->is_active ?? true ? 'active' : 'locked',
                'is_active' => $customer->user->is_active ?? true,
                'created_at' => $customer->created_at,
            ];
        });

        return response()->json([ 'success' => true, 'customers' => $customers ]);
    }

    // POST /owner/customers/toggle
    public function toggleCustomer(Request $request)
    {
        $this->authorizeOwner();

        $validated = $request->validate([
            'id' => ['required','string'], // Customer ID
            'action' => ['required','string','in:lock,ban'], // lock or ban
            'enabled' => ['required','boolean'],
        ]);

        $customer = Customer::with('user')->where('customer_id', $validated['id'])->first();
        
        if (!$customer || !$customer->user) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $user = $customer->user;

        // For now, we'll use is_active to represent both lock and ban
        // In the future, you might want to add separate fields for banned vs locked
        if ($validated['action'] === 'lock' || $validated['action'] === 'ban') {
            $user->is_active = !$validated['enabled']; // If enabling lock/ban, set active to false
            $user->save();

            // Force logout if locked/banned
            if ($validated['enabled']) {
                $this->forceLogoutUserSessions($user);
            }
        }

        $actionText = $validated['action'] === 'ban' ? 'banned' : 'locked';
        $message = $validated['enabled'] 
            ? "Customer {$actionText}" 
            : "Customer un{$actionText}";

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    // POST /owner/users
    public function store(Request $request)
    {
        $this->authorizeOwner();

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'username' => ['required','string','max:255', 'unique:users,username'],
            'email' => ['required','email','max:255', 'unique:employees,email'],
            // prevent creation of owner/admin via this UI
            'role' => ['required', Rule::in(['manager','cashier'])],
            'password' => ['nullable','string','min:8','confirmed'],
        ]);

        $password = $validated['password'] ?? 'password';

        DB::beginTransaction();
        try {
            // Create user account (authentication only)
            $user = User::create([
                'username' => $validated['username'],
                'role' => $validated['role'],
                'is_active' => true,
                'password' => Hash::make($password),
            ]);

            // Create employee record with profile data
            $employee = Employee::create([
                'user_id' => $user->user_id,
                'fullname' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => '', // Empty for now, can be updated later
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'user' => [
                    'id' => $employee->employee_id,
                    'name' => $employee->fullname,
                    'username' => $user->username,
                    'email' => $employee->email,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST /owner/users/toggle
    public function toggle(Request $request)
    {
        $this->authorizeOwner();

        $validated = $request->validate([
            'id' => ['required','string'], // Employee ID or User ID
            'enabled' => ['required','boolean'],
        ]);

        $user = null;
        
        // Try to find by Employee ID first
        $employee = Employee::with('user')->where('employee_id', $validated['id'])->first();
        
        if ($employee && $employee->user) {
            $user = $employee->user;
        } else {
            // Fallback: try to find by User ID directly
            $user = User::where('user_id', $validated['id'])->first();
        }
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own active status.',
            ], 422);
        }

        // Protect owner/admin accounts from being toggled via this UI
        if (in_array($user->role, ['owner', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change status of owner or admin accounts via this interface.',
            ], 403);
        }

        $user->is_active = $validated['enabled'];
        $user->save();

        // Best-effort force logout: if disabled, invalidate sessions where possible
        if (!$validated['enabled']) {
            $this->forceLogoutUserSessions($user);
        }

        return response()->json([
            'success' => true,
            'message' => $validated['enabled'] ? 'User enabled' : 'User disabled (will be logged out)',
        ]);
    }

    protected function authorizeOwner(): void
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['owner','admin'])) {
            abort(403);
        }
    }

    protected function forceLogoutUserSessions(User $user): void
    {
        // If using database sessions and table exists, delete rows for this user
        try {
            if (config('session.driver') === 'database' && \Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        } catch (\Throwable $e) {
            // Silently ignore if sessions table not present or other errors occur
        }

        // Additionally, rotate remember_token to invalidate remember-me cookies
        try {
            $user->setRememberToken(str()->random(60));
            $user->save();
        } catch (\Throwable $e) {
            // ignore
        }
    }

    // GET /owner/customers
    public function customersIndex(Request $request)
    {
        $this->authorizeOwner();

        $q = trim((string) $request->get('search', ''));
        
        // Get customers with their user data
        $customerQuery = Customer::with(['user' => function($query) {
            $query->select('user_id', 'username', 'role', 'is_active', 'created_at');
        }])->select(['customer_id', 'fullname', 'email', 'phone_number', 'user_id', 'created_at']);
        
        if ($q !== '') {
            $customerQuery->where(function($w) use ($q){
                $w->where('fullname','like',"%$q%")
                  ->orWhere('email','like',"%$q%")
                  ->orWhereHas('user', function($userQuery) use ($q) {
                      $userQuery->where('username', 'like', "%$q%");
                  });
            });
        }
        
        $customerData = $customerQuery->orderBy('fullname')->get();
        
        // Also get users with customer role that don't have customer records
        $userQuery = User::select(['user_id', 'username', 'role', 'is_active', 'created_at'])
            ->where('role', 'customer')
            ->whereNotIn('user_id', $customerData->pluck('user_id'));
            
        if ($q !== '') {
            $userQuery->where('username','like',"%$q%");
        }
        
        $userData = $userQuery->orderBy('username')->get();
        
        // Combine both customer records and user-only records
        $customers = collect();
        
        // Add customers with full records
        $customerData->each(function ($customer) use ($customers) {
            $customers->push([
                'id' => $customer->customer_id,
                'name' => $customer->fullname,
                'username' => $customer->user->username ?? '',
                'email' => $customer->email,
                'phone' => $customer->phone_number,
                'role' => $customer->user->role ?? 'customer',
                'is_active' => $customer->user->is_active ?? true,
                'created_at' => $customer->user->created_at ?? $customer->created_at,
            ]);
        });
        
        // Add users without customer records
        $userData->each(function ($user) use ($customers) {
            $customers->push([
                'id' => $user->user_id,
                'name' => $user->username, // Fallback to username
                'username' => $user->username,
                'email' => 'Not set',
                'phone' => 'Not set',
                'role' => $user->role,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
            ]);
        });

        return response()->json([ 'success' => true, 'customers' => $customers->toArray() ]);
    }

    // POST /owner/customers/toggle
    public function customersToggle(Request $request)
    {
        $this->authorizeOwner();

        $validated = $request->validate([
            'id' => ['required','string'], // Customer ID or User ID
            'enabled' => ['required','boolean'],
        ]);

        $user = null;
        
        // Try to find by Customer ID first
        $customer = Customer::with('user')->where('customer_id', $validated['id'])->first();
        
        if ($customer && $customer->user) {
            $user = $customer->user;
        } else {
            // Fallback: try to find by User ID directly
            $user = User::where('user_id', $validated['id'])->first();
        }
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        // Only allow toggling customer accounts
        if ($user->role !== 'customer') {
            return response()->json([
                'success' => false,
                'message' => 'Only customer accounts can be toggled via this endpoint.'
            ], 403);
        }

        $user->is_active = $validated['enabled'];
        $user->save();

        // Best-effort force logout: if disabled, invalidate sessions where possible
        if (!$validated['enabled']) {
            $this->forceLogoutUserSessions($user);
        }

        return response()->json([
            'success' => true,
            'message' => $validated['enabled'] ? 'Customer enabled' : 'Customer disabled (will be logged out)',
        ]);
    }
}