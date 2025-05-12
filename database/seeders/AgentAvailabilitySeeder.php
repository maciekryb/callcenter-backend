<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Agent;
use App\Models\AgentAvailability;

class AgentAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $agents = Agent::all();
        $today = Carbon::today();

        foreach ($agents as $agent) {
            $insertData = [];

            for ($i = 0; $i < 6; $i++) {
                $date = $today->copy()->addDays($i);
                $availabilityStatus = $this->getRandomAvailabilityStatus();

                if ($availabilityStatus === AgentAvailability::AVAILABILITY_PARTIAL_DAY) {
                    $lengthInHours = rand(1, 23);
                    $maxLengthInHours = 24 - $lengthInHours;
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

            DB::table('agent_availabilities')->insert($insertData);
        }
    }

    private function getRandomAvailabilityStatus(): string
    {
        $statuses = [
            AgentAvailability::AVAILABILITY_PARTIAL_DAY,
            AgentAvailability::AVAILABILITY_FULL_DAY,
            AgentAvailability::AVAILABILITY_NOT_AVAILABLE,
        ];

        // 70% szans na PARTIAL_DAY, 10% na FULL_DAY, 20% na OFF
        $weights = [70, 10, 20];
        $random = rand(1, 100);

        $cumulative = 0;
        foreach ($statuses as $index => $status) {
            $cumulative += $weights[$index];
            if ($random <= $cumulative) {
                return $status;
            }
        }

        return AgentAvailability::AVAILABILITY_PARTIAL_DAY;
    }
}
