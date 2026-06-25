<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DivisionLeadershipController;
use App\Http\Controllers\EnrollmentDataController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\KpiDataController;
use App\Http\Controllers\ResourceDataController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SchoolTypeController;
use App\Http\Controllers\SubmissionsController;
use App\Http\Resources\UserResource;
use App\Models\EnrollmentData;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:login');
/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /**
     * Current logged in user
     */
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });
    Route::get('/user-returned-submissions', [UserController::class, 'returnedSubmissions']);
    Route::post('logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Throttled API Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('throttle:api')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Activity Logs
        |--------------------------------------------------------------------------
        */

        Route::get('activity-logs', [ActivityLogController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */

        Route::apiResource('users', UserController::class)
            ->except('destroy');

        Route::prefix('users')->group(function () {
            Route::post('{user}/change-status', [
                UserController::class,
                'changeStatus'
            ]);
            Route::post('{user}/change-password', [
                UserController::class,
                'changePassword'
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        Route::get('roles', [RoleController::class, 'index']);


        /*
        |--------------------------------------------------------------------------
        | Academic Years
        |--------------------------------------------------------------------------
        */

        Route::apiResource('academic-years', AcademicYearController::class)
            ->except('destroy');

        Route::prefix('academic-years')->group(function () {
            Route::put(
                '{academic_year}/change-status',
                [AcademicYearController::class, 'changeStatus']
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Division Leadership
        |--------------------------------------------------------------------------
        */

        Route::apiResource(
            'division-leaderships',
            DivisionLeadershipController::class
        );

        /*
        |--------------------------------------------------------------------------
        | Schools
        |--------------------------------------------------------------------------
        */

        Route::post('schools', [SchoolController::class, 'store']);
        Route::put('schools/{school}', [SchoolController::class, 'update']);
        Route::delete('schools/{school}', [SchoolController::class, 'destroy']);
        Route::post(
            'schools/{school}/upload-image',
            [SchoolController::class, 'uploadImage']
        );

        Route::apiResource('schools', SchoolController::class)
            ->except(['store', 'update', 'destroy']);

        

        /*
        |--------------------------------------------------------------------------
        | School Types
        |--------------------------------------------------------------------------
        */

        Route::get('school-types', [SchoolTypeController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | KPI Data
        |--------------------------------------------------------------------------
        */

        Route::get('kpi-data', [KpiDataController::class, 'index']);
        Route::get('kpi-data/{kpiData}',[KpiDataController::class, 'show']);
        Route::put('kpi-data', [KpiDataController::class, 'update']);
        Route::post('kpi-data', [KpiDataController::class, 'store']);
        Route::get('kpi-data-trends', [KpiDataController::class, 'kpiTrends']);

        /*
        |--------------------------------------------------------------------------
        | Announcements
        |--------------------------------------------------------------------------
        */

        Route::apiResource('announcements', AnnouncementController::class)->only(['index', 'store']);

        /*
        |--------------------------------------------------------------------------
        | Resource Data
        |--------------------------------------------------------------------------
        */
        Route::apiResource('resource-data', ResourceDataController::class)->only(['index', 'update', 'show']);


        /*
        |--------------------------------------------------------------------------
        | Submissions
        |--------------------------------------------------------------------------
        */
        Route::apiResource('submissions', SubmissionsController::class)->except('destroy',);
        Route::post('submissions/{submission}/approve', [SubmissionsController::class, 'approve']);
    
        Route::post('submissions/{submission}/return', [SubmissionsController::class, 'return']);

        Route::post('submissions/{submission}/edit-request', [SubmissionsController::class, 'requestEdit']);
        Route::post('submissions/{submission}/decline-request', [SubmissionsController::class, 'declineRequest']);
        Route::post('submissions/{submission}/approve-request', [SubmissionsController::class, 'approveRequest']);

        /*
        |--------------------------------------------------------------------------
        | Enrollment Data
        |--------------------------------------------------------------------------
        */
        Route::apiResource('enrollment-data', EnrollmentDataController::class)->only(['index', 'show','update', 'destroy']);


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */
        Route::get('notifications', [NotificationsController::class, 'index']);
        Route::put('/notifications/{notification}/read', [NotificationsController::class, 'markAsRead']);


        /*
        |--------------------------------------------------------------------------
        | Dashboard Data
        |--------------------------------------------------------------------------
        */
        
        Route::get('enrollment-data-dashboard', [EnrollmentDataController::class, 'dashboardEnrollmentData']);
        Route::get('resource-data-dashboard', [ResourceDataController::class, 'dashboardResourceData']);


        /*
        |--------------------------------------------------------------------------
        | Public Routes [For ]
        |--------------------------------------------------------------------------
        */
        Route::group(['prefix' => 'public'], function ($r) {
            // $r->get('home', [HomeController::class, 'index'])->name('home.index');
            $r->get('announcements', [AnnouncementController::class, 'publicIndex'])->name('announcements.publicIndex');
            $r->get('announcements/{announcement}', [AnnouncementController::class, 'publicShow'])->name('announcements.publicShow');
            // $r->get('schools', [SchoolController::class, 'publicIndex'])->name('schools.publicIndex');
            // $r->get('schools/{school}', [SchoolController::class, 'publicShow'])->name('schools.publicShow');
            // $r->get('enrollment-data', [EnrollmentDataController::class, 'publicIndex'])->name('enrollment-data.publicIndex');
            // $r->get('resource-data', [ResourceDataController::class, 'publicIndex'])->name('resource-data.publicIndex');
            // $r->get('kpi-data', [KpiDataController::class, 'publicIndex'])->name('kpi-data.publicIndex');
            // $r->get('division-leaderships', [DivisionLeadershipController::class, 'publicIndex'])->name('division-leaderships.publicIndex');
        });
    });
});