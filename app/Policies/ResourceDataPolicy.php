<?php

namespace App\Policies;

use App\Models\ResourceData;
use App\Models\User;

class ResourceDataPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {

        return ($user->hasRole(['System Admin', 'Division Admin', 'School Account']) && $user->is_active == true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return ($user->hasRole(['System Admin', 'Division Admin', 'School Account']) && $user->is_active == true);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['School Account']) && $user->is_active == true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->hasRole(['School Account']) && $user->is_active == true;
    }

    public function dashboard(User $user): bool
    {
        return $user->hasRole(['System Admin', 'School Account', 'Division Admin']) && $user->is_active == true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(['System Admin', 'School Account', 'Division Admin']) && $user->is_active == true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user): bool
    {
        return false;
    }
}