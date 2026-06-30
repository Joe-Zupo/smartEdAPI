<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Policies\UserPolicy;

class RoleController extends Controller
{
     /**
     * Index Roles
     */
    public function index()
    {
        $this->authorize('viewAnyRoles', User::class);
        return $this->success('Roles fetched successfully', [
            "roles" => Role::all()
        ]);
    }
}
