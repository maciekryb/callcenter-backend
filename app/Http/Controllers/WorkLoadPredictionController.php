<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WorkLoadPrediction;

class WorkLoadPredictionController extends Controller
{
    public function getWorkLoadPredictionByQueueId($id)
    {
        $validated = validator(['id' => $id], [
            'id' => 'required|integer|exists:queues,id',
        ])->validate();

        //Na potrzeby MVP pobieramy obecny tydzień
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $schedules = WorkLoadPrediction::getByQueueIdAndDates($validated['id'], $startDate, $endDate);

        $schedules = $this->groupByDateAndHour($schedules);
        return response()->json($schedules);
    }

    private function groupByDateAndHour($schedules)
    {
        $grouped = [];

        foreach ($schedules as $item) {
            $date = $item->date;
            $hour = (int)$item->start_hour;
            $count = (int)$item->phone_calls_per_hour;

            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = [
                'hour' => $hour,
                'count' => $count,
            ];
        }

        return $grouped;
    }
}
