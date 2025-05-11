<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentAvailability extends Model
{
    use HasFactory;

    public static function getAllAgentScheduleForDateRange($startDate, $endDate)
    {
        return self::whereBetween('date', [$startDate, $endDate])->get();
    }
}
