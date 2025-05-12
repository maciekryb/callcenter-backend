<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AgentAvailability;

class AgentAvailabilityController extends Controller
{
    public function getAgentsScheduleByQueueId( $id)
    {
        $validated = validator(['id' => $id], [
            'id' => 'required|integer|exists:queues,id',
        ])->validate();

        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $schedules = AgentAvailability::getAgentsScheduleByQueueId($validated['id'], $startDate, $endDate);

        return response()->json($schedules);
    }
}
