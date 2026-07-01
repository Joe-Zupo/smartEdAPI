<?php

namespace App\Policies;

use App\Models\EnrollmentData;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EnrollmentDataPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {

        return ($user->hasRole(['System Admin', 'Division Admin', 'School Account']) && $user->is_active == true)
        ? Response::allow()
        : Response::deny('You do not have permission to view enrollment data.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user)
    {
        return ($user->hasRole(['System Admin', 'Division Admin', 'School Account']) && $user->is_active == true)
        ? Response::allow()
        : Response::deny('You do not have permission to view enrollment data.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->hasRole(['School Account']) && $user->is_active == true
        ? Response::allow()
        : Response::deny('You do not have permission to create enrollment data.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user)
    {
        return $user->hasRole(['School Account']) && $user->is_active == true
        ? Response::allow()
        : Response::deny('You do not have permission to update enrollment data.');
    }

    public function dashboard(User $user)
    {
        return $user->hasRole(['System Admin', 'School Account', 'Division Admin']) && $user->is_active == true
        ? Response::allow()
        : Response::deny('You do not have permission to view enrollment data.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user)
    {
        return $user->hasRole(['System Admin', 'School Account', 'Division Admin']) && $user->is_active == true
        ? Response::allow()
        : Response::deny('You do not have permission to delete enrollment data.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user)
    {
        return false;
    }
}