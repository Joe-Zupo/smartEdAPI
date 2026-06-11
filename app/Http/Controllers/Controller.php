<?php

namespace App\Http\Controllers;

use App\Helpers\autoPaginator;
use App\Helpers\images;
use App\Helpers\responseAPI;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Controller
{
    use autoPaginator, responseAPI, AuthorizesRequests, DispatchesJobs, images;
}
