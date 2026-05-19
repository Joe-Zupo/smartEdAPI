<?php

namespace App\Http\Controllers;

use App\autoPaginator;
use App\responseAPI;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use autoPaginator, responseAPI, AuthorizesRequests;
}
