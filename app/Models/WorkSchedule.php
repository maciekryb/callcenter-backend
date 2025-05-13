<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    use HasFactory;

    const AVAILABILITY_FULL_DAY = "full_day";
    const AVAILABILITY_PARTIAL_DAY = "partial_day";

    const validAvailabilityStatus = [
        self::AVAILABILITY_FULL_DAY,
        self::AVAILABILITY_PARTIAL_DAY,
    ];
}
