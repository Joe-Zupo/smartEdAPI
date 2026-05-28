<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DivisionLeadershipController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\KpiDataController;
use App\Http\Resources\UserResource;

/**
 * Current logged in user
 */
Route::get('/user', function (Request $request) {
    return new UserResource($request->user());
})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Activity Logs
Route::get('activity-logs', [ActivityLogController::class, 'index'])->middleware(['auth:sanctum', 'throttle:api']);
// Route::get('activity-logs/recent', [ActivityLogController::class, 'recent'])->middleware('auth:sanctum');


//User Controller Endpoints
Route::apiResource('users', UserController::class)->except('destroy')->middleware(['auth:sanctum', 'throttle:api']);
Route::group([
    'middleware' => ['auth:sanctum', 'throttle:api'],
    'prefix' => 'users'
    ], function ($r) {
    $r->post('/{user}/change-status', [UserController::class, 'changeStatus']);
    $r->post('/{user}/change-password', [UserController::class, 'changePassword']);
});

//Academic Year Controller Endpoints
Route::apiResource('academic-years',  AcademicYearController::class)->except('destroy')->middleware(['auth:sanctum', 'throttle:api']);
Route::group([
    'middleware' => ['auth:sanctum', 'throttle:api'],
    'prefix' => 'academic-years'
    ], function ($r){
        $r->post('{academic_year}/change-status', [AcademicYearController::class, 'changeStatus']);
    });


//Div Lead Controller
Route::apiResource('division-leaderships', DivisionLeadershipController::class)->middleware(['auth:sanctum', 'throttle:api']);

// schools routes 
Route::middleware('auth:sanctum')->group(function ($r) {
    $r->post('schools', [SchoolController::class, 'store'])->name('schools.store');
    $r->put('schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
    $r->apiResource('schools', SchoolController::class)->except(['store', 'update', 'destroy']);
    $r->delete('schools/{school}', [SchoolController::class, 'destroy']);
    $r->post('schools/{school}/upload-image', [SchoolController::class, 'uploadImage']);

// KPI data routes
Route::middleware('auth:sanctum')->group(function () {
    // Route::post('kpi-data', [KpiDataController::class, 'store'])->name('kpi-data.store');
    // Route::put('kpi-data', [KpiDataController::class, 'update'])->name('kpi-data.update');
    Route::get('kpi-data', [KpiDataController::class, 'index']);
    Route::get('kpi-data/{kpiData}', [KpiDataController::class, 'show']);
    // Route::delete('kpi-data/{kpi_data}', [KpiDataController::class, 'destroy'])->name('kpi-data.destroy');
});
});
