<?php

namespace App\Http\Controllers;

use App\Models\SchoolType;
use Illuminate\Http\Request;
use App\Policies\SchoolTypePolicy;

class SchoolTypeController extends Controller
{
    /**
     * School Types Index
     */
    public function index(){
        $this->authorize('viewAny', SchoolType::class);
        $query = SchoolType::query();

        $schoolTypes = $query->get();

        if ($schoolTypes->isEmpty()) {
            return $this->error(['message' => 'No school types found, please seed']);
        }

        return $this->success('School types retrieved successfully', ["school_types" => $schoolTypes]);
    }
}
