<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Agent;
use App\Models\AgentAvailability; // Upewnij się, że masz model AgentAvailability

class AgentAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $agents = Agent::all();
        $today = Carbon::today();

        foreach ($agents as $agent) {
            $insertData = []; // Przygotowanie danych do grupowego wstawienia

            for ($i = 0; $i < 6; $i++) {
                $date = $today->copy()->addDays($i);
                $availabilityStatus = $this->getRandomAvailabilityStatus();

                if ($availabilityStatus === AgentAvailability::AVAILABILITY_PARTIAL_DAY) {
                    $lengthInHours = rand(1, 23); //czas dyżuru
                    $maxLengthInHours = 24 - $lengthInHours; // do której godziny maksymalnie można przydzielić dyżur
                    $startHour = rand(0, $maxLengthInHours);
                    $endHour = $startHour + $lengthInHours;
                    $insertData[] = [
                        'agent_id' => $agent->id,
                        'date' => $date->toDateString(),
                        'availability_status' => $availabilityStatus,
                        'start_time' => Carbon::createFromTime($startHour)->format('H:i:s'),
                        'end_time' => Carbon::createFromTime($endHour)->format('H:i:s'),
                        'notes' => null,
                        'created_at' => now(),
                    ];
                } else {
                    $insertData[] = [
                        'agent_id' => $agent->id,
                        'date' => $date->toDateString(),
                        'availability_status' => $availabilityStatus,
                        'start_time' => null,
                        'end_time' => null,
                        'notes' => null,
                        'created_at' => now(),
                    ];
                }
            }

            // Grupowe wstawienie danych
            DB::table('agent_availabilities')->insert($insertData);
        }
    }

    /**
     * Losuje typ dostępności z większym prawdopodobieństwem dla AVAILABILITY_PARTIAL_DAY.
     */
    private function getRandomAvailabilityStatus(): string
    {
        $statuses = [
            AgentAvailability::AVAILABILITY_PARTIAL_DAY,
            AgentAvailability::AVAILABILITY_FULL_DAY,
            AgentAvailability::AVAILABILITY_NOT_AVAILABLE,
        ];

        // 70% szans na PARTIAL_DAY, 15% na FULL_DAY, 15% na OFF
        $weights = [70, 15, 15];
        $random = rand(1, 100);

        $cumulative = 0;
        foreach ($statuses as $index => $status) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $status;
            }
        }

        return AgentAvailability::AVAILABILITY_PARTIAL_DAY; // Domyślnie PARTIAL_DAY
    }
}
