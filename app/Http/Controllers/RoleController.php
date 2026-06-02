<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
     /**
     * Index Roles
     */
    public function index()
    {
        
        return $this->success('Roles fetched successfully', [
            "roles" => Role::all()
        ]);
    }
}
