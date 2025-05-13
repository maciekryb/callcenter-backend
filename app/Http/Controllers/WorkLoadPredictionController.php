<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WorkLoadPrediction;

class WorkLoadPredictionController extends Controller
{
    public function getAgentsScheduleByQueueId($id)
    {
        $validated = validator(['id' => $id], [
            'id' => 'required|integer|exists:queues,id',
        ])->validate();

        //Na potrzeby MVP pobieramy obecny tydzień
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $schedules = WorkLoadPrediction::getByQueueIdAndDates($validated['id'], $startDate, $endDate);

        return response()->json($schedules);
    }
}
