<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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


    public static function getAgentsScheduleByQueueId($id, $startDate, $endDate)
    {
        $agentIds = DB::table('agent_queues')
            ->where('queue_id', $id)
            ->pluck('agent_id');

        $schedules = self::with(['agent.queues' => function () {}])
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('agent_id', $agentIds)
            ->get();


        $schedules = $schedules->map(function ($availability) {
            if ($availability->agent && $availability->agent->queues) {
                $availability->agent->queues = $availability->agent->queues->map(function ($queue) {
                    unset($queue["pivot"]);
                    return $queue;
                });
            }
            return $availability;
        });
        return $schedules;
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
