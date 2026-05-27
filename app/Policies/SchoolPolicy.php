<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SchoolPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasRole(['System Admin', 'Division Admin'])
            ? Response::allow()
            : response::deny('You do not have permission to view users.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, School $school)
    {
         return $user->hasRole(['System Admin', 'Division Admin']) || $user->school_id === $school->id
            ? Response::allow()
            : response::deny('You do not have permission to view users.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
         return $user->hasRole(['System Admin'])
            ? Response::allow()
            : response::deny('You do not have permission to view users.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, School $school)
    {
         return $user->hasRole(['System Admin']) || $user->school_id === $school->id
            ? Response::allow()
            : response::deny('You do not have permission to view users.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, School $school): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, School $school): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, School $school): bool
    {
        return false;
    }
}
