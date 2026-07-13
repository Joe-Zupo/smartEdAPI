<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class BroadcastMetaController extends Controller
{
    /**
     * Broadcast Metadata
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Broadcast metadata retrieved successfully',
            'data' => [
                'channels' => [
                    [
                        'name' => 'public.enrollment',
                        'pusher_channel' => 'public.enrollment',
                        'access' => 'public',
                        'authorization' => 'No auth required',
                        'events' => ['PublicEnrollmentTotalsChanged,PublicEnrollmentGradesChanged,PublicEnrollmentLevelsChanged,PublicEnrollmentTrendsChanged'],
                        'description' => 'Broadcasts approved enrollment totals by academic year; Data is only usable for public routes for enrollment.',
                    ],
                    [
                        'name' => 'public.resource-data',
                        'pusher_channel' => 'public.resource-data',
                        'access' => 'public',
                        'authorization' => 'No auth required',
                        'events' => ['PublicResourceTotalsChanged'],
                        'description' => 'Broadcasts approved resource data totals by academic year; Data is only usable for public routes for resource data.',
                    ],
                ],
            ],
        ]);
    }
}
