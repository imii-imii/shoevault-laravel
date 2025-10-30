<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $builder = User::query()->select(['id','name','username','email','role','is_active','created_at'])
            ->whereNotIn('role', ['owner', 'admin']);
        if ($q !== '') {
            $builder->where(function($w) use ($q){
                $w->where('name','like',"%$q%")
                  ->orWhere('username','like',"%$q%")
                  ->orWhere('email','like',"%$q%")
                  ->orWhere('role','like',"%$q%");
            });
        }
        $users = $builder->orderBy('name')->get();

        return response()->json([ 'success' => true, 'users' => $users ]);
    }

    // POST /owner/users
    public function store(Request $request)
    {
        $this->authorizeOwner();

        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'username' => ['required','string','max:255', 'unique:users,username'],
            'email' => ['required','email','max:255', 'unique:users,email'],
            // prevent creation of owner/admin via this UI
            'role' => ['required', Rule::in(['manager','cashier'])],
            'password' => ['nullable','string','min:8','confirmed'],
        ]);

        $password = $validated['password'] ?? 'password';

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => true,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at,
            ],
        ], 201);
    }

    // POST /owner/users/toggle
    public function toggle(Request $request)
    {
        $this->authorizeOwner();

        $validated = $request->validate([
            'id' => ['required','integer','exists:users,id'],
            'enabled' => ['required','boolean'],
        ]);

        $user = User::findOrFail($validated['id']);

        if ($user->id === Auth::id()) {
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
}