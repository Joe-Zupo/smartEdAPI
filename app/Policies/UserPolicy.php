<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{

    public function viewAny(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to view users.'
            );
    }

    public function viewAnyLogs(User $user)
    {
        return ($user->hasRole(['System Admin', 'School Account', 'Division Admin']) && $user->is_active == true)
            ? Response::allow()
            : Response::deny('You do not have permission to activity logs.');
    }

    public function view(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to view users.'
            );
    }

    public function create(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to create users.'
            );
    }

    public function update(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to update users.'
            );
    }

    public function changePW(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to change user passwords.'
            );
    }

    public function toggleStatus(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to change user statuses.'
            );
    }

    public function viewAnyRoles(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
            'School Account',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to view roles.'
            );
    }
}
