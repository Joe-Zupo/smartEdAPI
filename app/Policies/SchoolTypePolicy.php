<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SchoolType;
use Illuminate\Auth\Access\Response;

class SchoolTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return $user->hasRole(['System Admin', 'Division Admin', 'School Account'])
            ? Response::allow()
            : response::deny('You do not have permission to view schools.');
    }

}
