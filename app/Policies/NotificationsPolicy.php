<?php

namespace App\Policies;

use App\Models\Notifications;
use App\Models\User;

class NotificationsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {

        return ($user->hasRole(['School Account','System Admin', 'Division Admin']) && $user->is_active == true);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function customFunc(User $user): bool
    {

        return ($user->hasRole(['School Account','System Admin', 'Division Admin']) && $user->is_active == true);
    }
}
