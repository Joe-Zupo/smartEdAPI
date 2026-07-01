<?php

namespace App\Policies;

use App\Models\ResourceData;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ResourceDataPolicy
{

    public function viewAny(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
            'School Account',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to view resource data.'
            );
    }

    public function view(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
            'School Account',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to view resource data.'
            );
    }

    public function create(User $user)
    {
        return $user->hasRole('School Account') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to create resource data.'
            );
    }

    public function update(User $user)
    {
        return $user->hasRole('School Account') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to update resource data.'
            );
    }

    public function dashboard(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
            'School Account',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to access the resource dashboard.'
            );
    }

    public function delete(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
            'School Account',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to delete resource data.'
            );
    }

    public function restore(User $user)
    {
        return Response::deny(
            'Resource data cannot be restored.'
        );
    }

    public function forceDelete(User $user)
    {
        return Response::deny(
            'Resource data cannot be permanently deleted.'
        );
    }
}