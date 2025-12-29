<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated can view lists
    }

    public function view(User $user, Customer $customer): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role === 'Sales Manager';
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->role === 'Sales Manager';
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->role === 'Sales Manager';
    }

    // Add for orders, credit, etc., if needed
}