<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AgentAvailability;
use App\Services\AgentService;
use Illuminate\Http\Request;

class AgentAvailabilityController extends Controller
{
    public function getAllAgentsSchedule(Request $request)
    {
        $startDate =  now()->startOfWeek();
        $endDate =  now()->endOfWeek();

        $schedules = AgentAvailability::getAllAgentScheduleForDateRange($startDate, $endDate);

        $schedules = $schedules->map(function ($availability) {
            if ($availability->agent && $availability->agent->queues) {
                $availability->agent->queues = $availability->agent->queues->map(function ($queue) {
                    unset($queue["pivot"]);
                    return $queue;
                });
            }
            return $availability;
        });

        return response()->json($schedules);
    }

    public function getAgentsScheduleByQueueId($id)
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $schedules = AgentAvailability::getAgentsScheduleByQueueId($id, $startDate, $endDate);

        return response()->json($schedules);
    }
}
