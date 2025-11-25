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
        
        // Get employees with their user data (exclude owner and customer accounts)
        $employeeQuery = Employee::with(['user' => function($query) {
            $query->select('user_id', 'username', 'role', 'is_active', 'created_at');
        }])->whereHas('user', function($userQuery) {
            $userQuery->whereNotIn('role', ['owner', 'customer']);
        })->select(['employee_id', 'fullname', 'email', 'phone_number', 'user_id', 'created_at']);
        
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
        
        // Also get users with employee roles that don't have employee records (exclude owner and customer)
        $userQuery = User::select(['user_id', 'username', 'role', 'is_active', 'created_at'])
            ->whereNotIn('role', ['owner', 'customer'])
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
            $isLocked = $customer->isLocked();
            $isBanned = !($customer->user->is_active ?? true);
            
            return [
                'id' => $customer->customer_id,
                'name' => $customer->fullname,
                'username' => $customer->user->username ?? '',
                'email' => $customer->email,
                'phone' => $customer->phone_number,
                'status' => $isBanned ? 'banned' : ($isLocked ? 'locked' : 'active'),
                'is_active' => $customer->user->is_active ?? true,
                'is_locked' => $isLocked,
                'is_restricted' => $customer->isRestricted(),
                'restricted_until' => $customer->restricted_until,
                'restriction_reason' => $customer->restriction_reason,
                'lock_status' => $customer->lock_status,
                'days_remaining' => $customer->days_remaining,
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
            'days' => ['nullable','integer','min:1','max:365'], // Duration for temporary lock
            'reason' => ['nullable','string','max:500'] // Optional reason
        ]);

        $customer = Customer::with('user')->where('customer_id', $validated['id'])->first();
        
        if (!$customer || !$customer->user) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        if ($validated['action'] === 'lock') {
            if ($validated['enabled']) {
                // Apply lock using restriction system (temporary or permanent)
                $days = $validated['days']; // Keep null for permanent locks
                $reason = $validated['reason'] ?? 'Account locked by admin';
                $restrictedBy = Auth::user()->username ?? 'System Admin';
                
                $customer->restrict($days, "[LOCKED] {$reason}", $restrictedBy);
                
                // Force logout the customer
                if ($customer->user) {
                    $this->forceLogoutUserSessions($customer->user);
                }
                
                $message = $days ? "Customer locked for {$days} days" : "Customer permanently locked";
            } else {
                // Unlock by lifting restriction if it's a lock
                if ($customer->isRestricted() && $customer->restriction_reason && str_starts_with($customer->restriction_reason, '[LOCKED]')) {
                    $customer->liftRestriction();
                    $message = "Customer unlocked";
                } else {
                    $message = "Customer was not locked";
                }
            }
        } elseif ($validated['action'] === 'ban') {
            // Keep ban functionality using is_active for permanent bans
            $user = $customer->user;
            $user->is_active = !$validated['enabled'];
            $user->save();

            if ($validated['enabled']) {
                $this->forceLogoutUserSessions($user);
            }
            
            $message = $validated['enabled'] ? "Customer banned" : "Customer unbanned";
        }

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

        // Set role-based default password if no password provided
        $defaultPasswords = [
            'manager' => 'manager123',
            'cashier' => 'cashier123',
        ];
        
        $password = $validated['password'] ?? ($defaultPasswords[$validated['role']] ?? 'password123');

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
                'default_password' => isset($validated['password']) ? null : $password, // Only show if default was used
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

        // Protect owner accounts from being toggled via this UI
        if (in_array($user->role, ['owner'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change status of owner accounts via this interface.',
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
        if (!$user || !in_array($user->role, ['owner'])) {
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
        
        // Get customers with their user data including restriction fields
        $customerQuery = Customer::with(['user' => function($query) {
            $query->select('user_id', 'username', 'role', 'is_active', 'created_at');
        }])->select(['customer_id', 'fullname', 'email', 'phone_number', 'user_id', 'created_at', 
                    'is_restricted', 'restricted_until', 'restriction_reason', 'restricted_by', 'restricted_at']);
        
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
                'is_restricted' => $customer->is_restricted ?? false,
                'restricted_until' => $customer->restricted_until,
                'restriction_reason' => $customer->restriction_reason,
                'restricted_by' => $customer->restricted_by,
                'restricted_at' => $customer->restricted_at,
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

    // POST /api/customers/restrict
    public function restrictCustomer(Request $request)
    {
        $this->authorizeOwner();

        $validated = $request->validate([
            'customer_id' => ['required', 'string'],
            'restrict' => ['required', 'boolean'],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'reason' => ['nullable', 'string', 'max:1000']
        ]);

        $customer = Customer::where('customer_id', $validated['customer_id'])->first();
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        if ($validated['restrict']) {
            // Apply restriction
            $days = $validated['days'];
            $reason = $validated['reason'] ?? 'No reason provided';
            $restrictedBy = Auth::user()->username ?? 'System Admin';
            
            $customer->restrict($days, $reason, $restrictedBy);
            
            // Force logout the customer if they have active sessions
            if ($customer->user) {
                $this->forceLogoutUserSessions($customer->user);
            }
            
            $message = $days 
                ? "Customer restricted for {$days} days" 
                : "Customer permanently restricted";
                
        } else {
            // Lift restriction
            $customer->liftRestriction();
            $message = "Customer restriction lifted";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    // POST /owner/users/reset-password
    public function resetPassword(Request $request)
    {
        $this->authorizeOwner();

        $validated = $request->validate([
            'id' => ['required', 'string'], // Employee ID or User ID
        ]);

        $user = null;
        $employeeName = '';
        
        // Try to find by Employee ID first
        $employee = Employee::with('user')->where('employee_id', $validated['id'])->first();
        
        if ($employee && $employee->user) {
            $user = $employee->user;
            $employeeName = $employee->fullname;
        } else {
            // Try to find by User ID
            $user = User::find($validated['id']);
            if ($user && $user->employee) {
                $employeeName = $user->employee->fullname;
            } else {
                $employeeName = $user->username ?? 'Unknown';
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Only allow resetting passwords for employee roles
        if (!in_array($user->role, ['manager', 'cashier', 'employee'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only employee passwords can be reset.'
            ], 403);
        }

        // Set default password based on role
        $defaultPassword = match($user->role) {
            'manager' => 'manager123',
            'cashier' => 'cashier123',
            'employee' => 'employee123',
            default => 'password123'
        };

        // Update the password
        $user->password = Hash::make($defaultPassword);
        $user->save();

        // Force logout the user to ensure they must login with new password
        $this->forceLogoutUserSessions($user);

        return response()->json([
            'success' => true,
            'message' => "Password for {$employeeName} has been reset to default. They will need to login again.",
            'default_password' => $defaultPassword
        ]);
    }
}