<?php

use Illuminate\Support\Facades\Broadcast;

//ENROLLMENT DATA
Broadcast::channel('public.enrollment', function ($user = null) {
    // totals are public data
    return true;
});

Broadcast::channel('admin.enrollment', function ($user) {
    return $user->hasAnyRole([
        'System Admin',
        'Division Admin',
    ]);
});

Broadcast::channel('school.enrollment.{schoolId}', function ($user, $schoolId) {
    return $user->hasRole('School Account')
        && $user->school_id == $schoolId;
});

//RESOURCE DATA
Broadcast::channel('public.resource-data', function ($user = null) {
    // resource totals are public data
    return true;
});

Broadcast::channel('admin.resource-data', function ($user){
    return $user->hasRole(['System Admin', 'Division Admin']);
});






Broadcast::channel('public.kpi-data', function ($user = null) {
    // KPI data records are public data
    return true;
});

Broadcast::channel('public.schools', function ($user) {
    // school data is public data
    return true;
});


