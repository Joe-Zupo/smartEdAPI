<?php

use Illuminate\Support\Facades\Route;

use Pusher\Pusher;

Route::get('/pusher-test', function () {
    $pusher = new Pusher(
        config('broadcasting.connections.pusher.key'),
        config('broadcasting.connections.pusher.secret'),
        config('broadcasting.connections.pusher.app_id'),
        [
            'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            'useTLS' => true,
        ]
    );

    $response = $pusher->trigger(
        'test-channel',
        'test-event',
        [
            'message' => 'Hello from Laravel!'
        ]
    );

    dd($response);
});