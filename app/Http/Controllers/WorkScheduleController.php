<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\WorkScheduleService;

class WorkScheduleController extends Controller
{
    public function __construct(protected WorkScheduleService $workScheduleService) {}

    public function create()
    {
        $date = now()->startOfWeek();
        $agent = $this->workScheduleService->createWorkSchedule($date);

        return response()->json($agent, 201);
    }
}
