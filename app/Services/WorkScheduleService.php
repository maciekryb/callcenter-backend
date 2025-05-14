<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Queue;
use Illuminate\Support\Facades\DB;
use App\Models\AgentAvailability;
use App\Models\WorkLoadPrediction;
use App\Models\WorkSchedule;

class WorkScheduleService
{

    public function getWorkScheduleByQueueId($id, $startDate, $endDate)
    {
        // Pobierz grafik agentów dla danej kolejki po id
        $workSchedule = WorkSchedule::with('agent')
            ->where('queue_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->map(function ($ws) {
                return [
                    'id' => $ws->id,
                    'agent_id' => $ws->agent_id,
                    'agent_name' => $ws->agent ? $ws->agent->name : null,
                    'queue_id' => $ws->queue_id,
                    'date' => $ws->date,
                    'start_time' => $ws->start_time,
                    'end_time' => $ws->end_time,
                ];
            });

        return $workSchedule;
    }

    public static function createWorkScheduleForWeek($startDate)
    {
        $queues = Queue::all();
        $results = [];
        $agentHourAssignments = [];
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = date('Y-m-d', strtotime($startDate . " +$i days"));
        }
        DB::beginTransaction();
        try {
            foreach ($days as $date) {
                foreach ($queues as $queue) {
                    $results = array_merge(
                        $results,
                        self::generateScheduleForQueueAndDay($queue, $date, $agentHourAssignments)
                    );
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $results;
    }

    private static function generateScheduleForQueueAndDay($queue, $date, &$agentHourAssignments)
    {
        $results = [];
        $predictions = WorkLoadPrediction::where('queue_id', $queue->id)
            ->where('date', $date)
            ->orderBy('start_hour')
            ->get();

        $agents = $queue->agents()->get();
        $agentEff = self::getAgentEfficiencies($agents);

        $availabilities = AgentAvailability::whereIn('agent_id', $agents->pluck('id'))
            ->where('date', $date)
            ->get()
            ->groupBy('agent_id');

        $hourlyNeed = self::getHourlyNeeds($predictions);

        arsort($hourlyNeed);

        foreach ($hourlyNeed as $hour => $callsNeeded) {
            $availableAgents = self::getAvailableAgents($agents, $availabilities, $hour);
            $agentEffective = self::getAgentEffectiveList($availableAgents, $agentEff, $agentHourAssignments, $date, $hour);

            usort($agentEffective, function ($a, $b) {
                return $b['effectiveEff'] <=> $a['effectiveEff'];
            });

            $callsLeft = $callsNeeded;
            foreach ($agentEffective as $item) {
                if ($callsLeft <= 0) break;
                $agent = $item['agent'];
                $eff = $item['effectiveEff'];

                WorkSchedule::updateOrCreate([
                    'queue_id' => $queue->id,
                    'agent_id' => $agent->id,
                    'date' => $date,
                    'start_time' => sprintf('%02d:00:00', $hour),
                ], [
                    'end_time' => sprintf('%02d:00:00', $hour + 1),
                ]);
                $results[] = [$queue->id, $agent->id, $date, $hour];
                $callsLeft -= $eff;
                $agentHourAssignments[$agent->id][$date][$hour] = ($agentHourAssignments[$agent->id][$date][$hour] ?? 0) + 1;
            }
        }
        return $results;
    }

    private static function getAgentEfficiencies($agents)
    {
        $agentEff = [];
        foreach ($agents as $agent) {
            $agentEff[$agent->id] = $agent->pivot->efficiency;
        }
        return $agentEff;
    }

    private static function getHourlyNeeds($predictions)
    {
        $hourlyNeed = [];
        foreach ($predictions as $prediction) {
            $hour = (int)substr($prediction->start_hour, 0, 2);
            $hourlyNeed[$hour] = $prediction->phone_calls_per_hour;
        }
        return $hourlyNeed;
    }

    private static function getAvailableAgents($agents, $availabilities, $hour)
    {
        $availableAgents = [];
        foreach ($agents as $agent) {
            $a = $availabilities[$agent->id][0] ?? null;
            if (!$a) continue;
            if ($a->availability_status === AgentAvailability::AVAILABILITY_NOT_AVAILABLE) continue;
            if ($a->availability_status === AgentAvailability::AVAILABILITY_FULL_DAY) {
                $availableAgents[] = $agent;
            } elseif ($a->availability_status === AgentAvailability::AVAILABILITY_PARTIAL_DAY) {
                if ($a->start_time && $a->end_time && $hour >= (int)substr($a->start_time, 0, 2) && $hour < (int)substr($a->end_time, 0, 2)) {
                    $availableAgents[] = $agent;
                }
            }
        }
        return $availableAgents;
    }

    private static function getAgentEffectiveList($availableAgents, $agentEff, $agentHourAssignments, $date, $hour)
    {
        $agentEffective = [];
        foreach ($availableAgents as $agent) {
            $effDiv = ($agentHourAssignments[$agent->id][$date][$hour] ?? 0) + 1;
            $effectiveEff = $agentEff[$agent->id] / $effDiv;
            if ($effectiveEff > 0) {
                $agentEffective[] = [
                    'agent' => $agent,
                    'effectiveEff' => $effectiveEff
                ];
            }
        }
        return $agentEffective;
    }
}
