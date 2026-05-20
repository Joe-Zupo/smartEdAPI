<?php

namespace App\Http\Controllers;

use App\autoPaginator;
use App\responseAPI;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Controller
{
    use autoPaginator, responseAPI, AuthorizesRequests, DispatchesJobs;
}
