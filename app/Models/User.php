<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\HasIncrementingId;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasIncrementingId;

    protected $primaryKey = 'user_id';

    /**
     * Get the prefix for the auto-generated ID.
     */
    protected function getIdPrefix(): string
    {
        return 'USR';
    }

    /**
     * Get the length of the number part (excluding prefix).
     */
    protected function getIdNumberLength(): int
    {
        return 6; // Will generate USR000001, USR000002, etc.
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'role',
        'is_active',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the employee record associated with the user.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id', 'user_id');
    }

    /**
     * Get the customer record associated with the user.
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'user_id', 'user_id');
    }

    /**
     * Get the profile record (employee or customer) for this user.
     */
    public function profile()
    {
        return $this->employee ?? $this->customer;
    }

    /**
     * Get the user's display name from their profile
     */
    public function getNameAttribute()
    {
        $profile = $this->profile();
        return $profile ? $profile->fullname : $this->username;
    }

    /**
     * Get the user's email from their profile
     */
    public function getEmailAttribute()
    {
        $profile = $this->profile();
        return $profile ? $profile->email : null;
    }

    /**
     * Get the user's phone from their profile
     */
    public function getPhoneAttribute()
    {
        $profile = $this->profile();
        return $profile ? $profile->phone_number : null;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is an employee
     */
    public function isEmployee()
    {
        return in_array($this->role, ['owner', 'admin', 'employee', 'staff']);
    }

    /**
     * Check if user is a customer
     */
    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for users by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
