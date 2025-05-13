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
    public function createWorkSchedule($date)
    {
        // Pobierz wszystkie kolejki
        $queues = Queue::all();
        $results = [];
        DB::beginTransaction();
        try {
            foreach ($queues as $queue) {
                // Pobierz prognozy obciążenia na dany dzień dla tej kolejki
                $predictions = WorkLoadPrediction::where('queue_id', $queue->id)
                    ->where('date', $date)
                    ->orderBy('start_hour')
                    ->get();

                // Pobierz agentów obsługujących tę kolejkę wraz z efektywnością
                $agents = $queue->agents()->get();
                $agentEff = [];
                foreach ($agents as $agent) {
                    $agentEff[$agent->id] = $agent->pivot->efficiency;
                }

                // Pobierz dostępności agentów na ten dzień
                $availabilities = AgentAvailability::whereIn('agent_id', $agents->pluck('id'))
                    ->where('date', $date)
                    ->get()
                    ->groupBy('agent_id');

                foreach ($predictions as $prediction) {
                    $needed = max(1, ceil($prediction->phone_calls_per_hour / 20)); // 1 agent na 20 połączeń
                    $hour = $prediction->start_hour;

                    // Filtruj dostępnych agentów na tę godzinę
                    $availableAgents = [];
                    foreach ($agents as $agent) {
                        $a = $availabilities[$agent->id][0] ?? null;
                        if (!$a) continue;
                        if ($a->availability_status === AgentAvailability::AVAILABILITY_NOT_AVAILABLE) continue;
                        if ($a->availability_status === AgentAvailability::AVAILABILITY_FULL_DAY) {
                            $availableAgents[] = $agent;
                        } elseif ($a->availability_status === AgentAvailability::AVAILABILITY_PARTIAL_DAY) {
                            if ($a->start_time && $a->end_time && $hour >= $a->start_time && $hour < $a->end_time) {
                                $availableAgents[] = $agent;
                            }
                        }
                    }
                    // Sortuj po efektywności malejąco
                    usort($availableAgents, function($a, $b) use ($agentEff) {
                        return $agentEff[$b->id] <=> $agentEff[$a->id];
                    });
                    // Przydziel agentów aż pokryjemy zapotrzebowanie
                    $assigned = 0;
                    foreach ($availableAgents as $agent) {
                        if ($assigned >= $needed) break;
                        WorkSchedule::updateOrCreate([
                            'queue_id' => $queue->id,
                            'agent_id' => $agent->id,
                            'date' => $date,
                            'start_time' => $hour,
                        ], [
                            'work_status' => WorkSchedule::AVAILABILITY_PARTIAL_DAY,
                            'end_time' => date('H:i:s', strtotime($hour) + 3600), // 1h slot
                        ]);
                        $assigned++;
                        $results[] = [$queue->id, $agent->id, $date, $hour];
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return $results;
    }
}
