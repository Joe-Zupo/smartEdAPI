<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('public.enrollment', function ($user = null) {
    // totals are public data
    return true;
});

Broadcast::channel('public.resource-data', function ($user = null) {
    // resource totals are public data
    return true;
});

Broadcast::channel('public.kpi-data', function ($user = null) {
    // KPI data records are public data
    return true;
});

Broadcast::channel('public.schools', function ($user) {
    // school data is public data
    return true;
});
