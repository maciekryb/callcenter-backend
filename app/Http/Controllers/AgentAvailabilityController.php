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
        // Pobierz daty z zapytania
        // $startDate = $request->input('start_date');
        // $endDate = $request->input('end_date');

        $startDate =  now()->startOfWeek();
        $endDate =  now()->endOfWeek();

        // Pobierz grafik agentów dla zakresu dat
        $schedules = AgentAvailability::getAllAgentScheduleForDateRange($startDate, $endDate);

        return response()->json($schedules);
    }
}
