<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentAvailability extends Model
{
    use HasFactory;

    const AVAILABILITY_FULL_DAY = "full_day";
    const AVAILABILITY_PARTIAL_DAY = "partial_day";
    const AVAILABILITY_NOT_AVAILABLE = "not_available";

    const validAvailabilityStatus = [
        self::AVAILABILITY_FULL_DAY,
        self::AVAILABILITY_PARTIAL_DAY,
        self::AVAILABILITY_NOT_AVAILABLE,
    ];

    public static function getAllAgentScheduleForDateRange($startDate, $endDate)
    {
        return self::whereBetween('date', [$startDate, $endDate])->get();
    }
}
