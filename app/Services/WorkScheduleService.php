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

    logger($workSchedule);
        return $workSchedule;
    }

    // Pobierz prognozy połączeń na dany dzień (dla każdej godziny).
    // Pobierz agentów obsługujących tę kolejkę (z efektywnością).
    // Pobierz dostępność tych agentów na dany dzień.
    // Dla każdej godziny w prognozie:

    // Oblicz zapotrzebowanie na agentów (np. 1 agent na 20 połączeń).
    // Wybierz agentów dostępnych w tej godzinie (uwzględnij full_day/partial_day).
    // Dla każdego dostępnego agenta:
    // Sprawdź, do ilu kolejek jest już przypisany w tej godzinie.
    // Podziel jego efektywność przez liczbę kolejek (jeśli >1).
    // Posortuj dostępnych agentów malejąco po efektywności (po podziale).
    // Przydzielaj agentów do kolejki, aż suma ich efektywności pokryje zapotrzebowanie (lub do wyczerpania dostępnych).
    // Dla każdego przydzielonego agenta:
    // Zapisz wpis do work_schedules z odpowiednim work_status (full_day/partial_day).

    public static function createWorkSchedule2($date)
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
                    usort($availableAgents, function ($a, $b) use ($agentEff) {
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

    //

    // Priorytet dla godzin dziennych – najpierw obsadzamy godziny 8-18.
    // Ciągłość zmian – przydzielamy agentów do bloków godzin, nie pojedynczych godzin.
    // Unikamy dziur w grafiku – agent powinien mieć zmiany w jednym kawałku, nie rozrzucone po całym dniu.

    public static function createWorkSchedule($date)
    {

        logger('pobieram kolejki');
        $queues = Queue::all();
        $results = [];
        $agentHourAssignments = [];
        $DAY_START = 8;
        $DAY_END = 18;
        DB::beginTransaction();
        try {
            foreach ($queues as $queue) {
                $predictions = WorkLoadPrediction::where('queue_id', $queue->id)
                    ->where('date', $date)
                    ->orderBy('start_hour')
                    ->get();
                $agents = $queue->agents()->get();
                $agentEff = [];
                foreach ($agents as $agent) {
                    $agentEff[$agent->id] = $agent->pivot->efficiency;
                }
                $availabilities = AgentAvailability::whereIn('agent_id', $agents->pluck('id'))
                    ->where('date', $date)
                    ->get()
                    ->groupBy('agent_id');
                // Przygotuj mapę godzin -> zapotrzebowanie
                $hourlyNeed = [];
                foreach ($predictions as $prediction) {
                    $hour = (int)substr($prediction->start_hour, 0, 2);
                    $hourlyNeed[$hour] = max(1, ceil($prediction->phone_calls_per_hour / 20));
                }
                // Dla każdego agenta znajdź bloki dostępności
                foreach ($agents as $agent) {
                    $a = $availabilities[$agent->id][0] ?? null;
                    if (!$a || $a->availability_status === AgentAvailability::AVAILABILITY_NOT_AVAILABLE) continue;
                    $blocks = [];
                    if ($a->availability_status === AgentAvailability::AVAILABILITY_FULL_DAY) {
                        $blocks[] = ['start' => $DAY_START, 'end' => $DAY_END, 'status' => WorkSchedule::AVAILABILITY_FULL_DAY];
                    } elseif ($a->availability_status === AgentAvailability::AVAILABILITY_PARTIAL_DAY) {
                        $start = $a->start_time ? (int)substr($a->start_time, 0, 2) : $DAY_START;
                        $end = $a->end_time ? (int)substr($a->end_time, 0, 2) : $DAY_END;
                        $blocks[] = ['start' => $start, 'end' => $end, 'status' => WorkSchedule::AVAILABILITY_PARTIAL_DAY];
                    }
                    // Najpierw przydzielaj bloki dzienne (8-18)
                    foreach ($blocks as $block) {
                        $blockStart = max($block['start'], $DAY_START);
                        $blockEnd = min($block['end'], $DAY_END);
                        $assignedAny = false;
                        for ($h = $blockStart; $h < $blockEnd; $h++) {
                            if (($hourlyNeed[$h] ?? 0) <= 0) continue;
                            // Sprawdź ile kolejek już ma agent w tej godzinie
                            $effDiv = ($agentHourAssignments[$agent->id][$h] ?? 0) + 1;
                            $effectiveEff = $agentEff[$agent->id] / $effDiv;
                            if ($effectiveEff < 0.1) continue;
                            // Przydziel jeśli jest zapotrzebowanie
                            WorkSchedule::updateOrCreate([
                                'queue_id' => $queue->id,
                                'agent_id' => $agent->id,
                                'date' => $date,
                                'start_time' => sprintf('%02d:00:00', $h),
                            ], [
                                'end_time' => sprintf('%02d:00:00', $h + 1),
                            ]);
                            $results[] = [$queue->id, $agent->id, $date, $h, $block['status']];
                            $hourlyNeed[$h]--;
                            $agentHourAssignments[$agent->id][$h] = $effDiv;
                            $assignedAny = true;
                        }
                        // Jeśli agent dostał blok godzin, nie przydzielaj mu pojedynczych godzin nocnych
                    }
                }
                // Jeśli nadal są nieobsadzone godziny, próbuj przydzielić agentów do nocnych godzin (0-8, 18-23)
                foreach ($agents as $agent) {
                    $a = $availabilities[$agent->id][0] ?? null;
                    if (!$a || $a->availability_status === AgentAvailability::AVAILABILITY_NOT_AVAILABLE) continue;
                    $blocks = [];
                    if ($a->availability_status === AgentAvailability::AVAILABILITY_FULL_DAY) {
                        $blocks[] = ['start' => 0, 'end' => 24, 'status' => WorkSchedule::AVAILABILITY_FULL_DAY];
                    } elseif ($a->availability_status === AgentAvailability::AVAILABILITY_PARTIAL_DAY) {
                        $start = $a->start_time ? (int)substr($a->start_time, 0, 2) : 0;
                        $end = $a->end_time ? (int)substr($a->end_time, 0, 2) : 24;
                        $blocks[] = ['start' => $start, 'end' => $end, 'status' => WorkSchedule::AVAILABILITY_PARTIAL_DAY];
                    }
                    foreach ($blocks as $block) {
                        // Nocne godziny
                        for ($h = $block['start']; $h < $block['end']; $h++) {
                            if ($h >= $DAY_START && $h < $DAY_END) continue; // pomiń dzienne, już obsadzone
                            if (($hourlyNeed[$h] ?? 0) <= 0) continue;
                            $effDiv = ($agentHourAssignments[$agent->id][$h] ?? 0) + 1;
                            $effectiveEff = $agentEff[$agent->id] / $effDiv;
                            if ($effectiveEff < 0.1) continue;
                            WorkSchedule::updateOrCreate([
                                'queue_id' => $queue->id,
                                'agent_id' => $agent->id,
                                'date' => $date,
                                'start_time' => sprintf('%02d:00:00', $h),
                            ], [
                                'end_time' => sprintf('%02d:00:00', $h + 1),
                            ]);
                            $results[] = [$queue->id, $agent->id, $date, $h, $block['status']];
                            $hourlyNeed[$h]--;
                            $agentHourAssignments[$agent->id][$h] = $effDiv;
                        }
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
