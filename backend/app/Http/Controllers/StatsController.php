<?php

namespace App\Http\Controllers;

use App\Services\WinStatsService;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function __invoke(WinStatsService $stats): JsonResponse
    {
        return response()->json($stats->summary());
    }
}
