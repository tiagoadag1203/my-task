<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ParticipantAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ParticipantAnalyticsService $participantAnalyticsService
    ) {}

    public function analytics(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => $this->participantAnalyticsService->analytics($user),
        ]);
    }
}
