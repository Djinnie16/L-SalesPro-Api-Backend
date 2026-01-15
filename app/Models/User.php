<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'permissions',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
    ];

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if (!is_array($this->permissions)) {
            return false;
        }
        
        return in_array($permission, $this->permissions);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is a Sales Manager
     */
    public function isSalesManager(): bool
    {
        return $this->role === 'Sales Manager';
    }

    /**
     * Check if user is a Sales Representative
     */
    public function isSalesRepresentative(): bool
    {
        return $this->role === 'Sales Representative';
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * User has many notifications
     */
    public function customNotifications()
{
    return $this->hasMany(\App\Models\Notification::class);
}
}