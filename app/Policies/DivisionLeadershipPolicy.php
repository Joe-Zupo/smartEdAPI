<?php

namespace App\Policies;


use App\Models\DivisionLeadership;
use App\Models\User;
use Illuminate\Auth\Access\Response;


class DivisionLeadershipPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to view division leadership records.');
    }

    public function view(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to view division leadership records.');
    }

    public function create(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to create division leadership records.');
    }

    public function update(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to update division leadership records.');
    }

    public function delete(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to delete division leadership records.');
    }

    public function restore(User $user)
    {
        return Response::deny(
            'Division leadership records cannot be restored.'
        );
    }

    public function forceDelete(User $user)
    {
        return Response::deny(
            'Division leadership records cannot be permanently deleted.'
        );
    }
}
