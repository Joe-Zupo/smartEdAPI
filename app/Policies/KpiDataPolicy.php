<?php

namespace App\Policies;

use App\Models\KpiData;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KpiDataPolicy
{

    public function viewAny(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to view KPI data.'
            );
    }

    public function view(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to view KPI data.'
            );
    }

    public function create(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to create KPI data.'
            );
    }

    public function update(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to update KPI data.'
            );
    }

    public function delete(User $user)
    {
        return $user->hasAnyRole([
            'System Admin',
            'Division Admin',
        ]) && $user->is_active
            ? Response::allow()
            : Response::deny(
                'You do not have permission to delete KPI data.'
            );
    }

    public function restore(User $user)
    {
        return Response::deny(
            'KPI data cannot be restored.'
        );
    }

    public function forceDelete(User $user)
    {
        return Response::deny(
            'KPI data cannot be permanently deleted.'
        );
    }
}