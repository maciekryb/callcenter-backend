<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkLoadPrediction extends Model
{
    use HasFactory;

    public static function getByQueueIdAndDates($id, $startDate, $endDate)
    {
        $workload = self::query()
            ->where('queue_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return $workload;
    }
}
