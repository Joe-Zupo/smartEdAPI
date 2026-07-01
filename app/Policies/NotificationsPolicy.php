<?php

namespace App\Policies;

use App\Models\Notifications;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationsPolicy
{

public function viewAny(User $user)
{
    return $user->hasAnyRole([
            'School Account',
            'System Admin',
            'Division Admin',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to view notifications.'
            );
    }

    public function customFunc(User $user)
    {
        return $user->hasAnyRole([
            'School Account',
            'System Admin',
            'Division Admin',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to access notifications.'
            );
    }
}
