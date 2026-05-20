<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;


/**
 * Current logged in user
 */
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('activity-logs', [ActivityLogController::class, 'index'])->middleware('auth:sanctum');


// Route::group([
//     'middleware' => 'auth:sanctum',
//     'prefix' => 'users'
//     ], function ($r) {
//     $r->apiResource('/', UserController::class)->except('destroy');
//     $r->post('/{user}/change-status', [UserController::class, 'changeStatus'])->name('users.changeStatus');
//     $r->post('/{user}/change-password', [UserController::class, 'changePassword'])->name('users.changePassword');
// });
