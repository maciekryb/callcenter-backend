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

    public function getWorkScheduleByQueueId($id)
    {

        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();
        $workSchedule = $this->workScheduleService->getWorkScheduleByQueueId($id, $startDate, $endDate);

        return response()->json($workSchedule, 200);
    }
}
