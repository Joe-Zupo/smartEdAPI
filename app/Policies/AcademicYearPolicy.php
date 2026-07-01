<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AcademicYearPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
            'School Account',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to view academic years.');
    }

    public function view(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
            'School Account',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to view academic years.');
    }

    public function create(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to create academic years.');
    }

    public function update(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to update academic years.');
    }

    public function changeStatus(User $user)
    {
        return $user->hasRole('System Admin') && $user->is_active
            ? Response::allow()
            : Response::deny('You do not have permission to change academic year statuses.');
    }
}
