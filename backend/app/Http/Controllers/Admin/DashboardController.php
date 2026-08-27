<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __construct(private readonly AnalyticsService $analyticsService) {}

    public function summary(): JsonResponse
    {
        return response()->json(['data' => $this->analyticsService->summary()]);
    }

    public function analytics(): JsonResponse
    {
        return response()->json(['data' => $this->analyticsService->analytics()]);
    }

    public function export(): StreamedResponse
    {
        return $this->analyticsService->exportCsv();
    }
}
