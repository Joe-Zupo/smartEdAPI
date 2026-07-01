<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SubmissionPolicy
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
                'You do not have permission to view submissions.'
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
                'You do not have permission to view submissions.'
            );
    }

    public function create(User $user)
    {
        return $user->hasRole('School Account') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to create submissions.'
            );
    }

    public function update(User $user)
    {
        return $user->hasRole('School Account') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to update submissions.'
            );
    }

    public function adminFunc(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to perform administrative submission actions.'
            );
    }

    public function schoolFunc(User $user)
    {
        return $user->hasRole('School Account') && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to perform school submission actions.'
            );
    }

    public function restore(User $user)
    {
        return Response::deny(
            'Submissions cannot be restored.'
        );
    }

    public function forceDelete(User $user)
    {
        return Response::deny(
            'Submissions cannot be permanently deleted.'
        );
    }
}
