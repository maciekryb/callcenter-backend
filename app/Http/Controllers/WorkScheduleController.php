<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\WorkScheduleService;

class WorkScheduleController extends Controller
{
    public function __construct(protected WorkScheduleService $workScheduleService) {}

    public function createWorkSchedule()
    {
        $date = now()->startOfWeek();
        $schedule = $this->workScheduleService->createWorkScheduleForWeek($date);

        return response()->json($schedule, 201);
    }

    public function getWorkScheduleByQueueId($id)
    {

        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();
        $workSchedule = $this->workScheduleService->getWorkScheduleByQueueId($id, $startDate, $endDate);

        return response()->json($workSchedule, 200);
    }
}
