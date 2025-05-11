<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Agent; // Upewnij się, że masz model Agent

class AgentAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $agents = Agent::all();
        $today = Carbon::today();

        foreach ($agents as $agent) {
            // Dla każdego agenta twórz dostępność na kolejne 6 dni
            for ($i = 0; $i < 6; $i++) {
                $date = $today->copy()->addDays($i);

                // Przykładowe godziny dostępności (losowe okno przez cały dzien)
                $lengthInHours = rand(1, 24); //czas dyżuru
                $maxLengthInHours = 24 - $lengthInHours; // do której godziny maksymalnie można przydzielić dyżur
                $startHour = rand(0, $maxLengthInHours);
                $endHour = $startHour + $lengthInHours; // 4–8 godzin zmiany

                DB::table('agent_availability')->insert([
                    'agent_id' => $agent->id,
                    'date' => $date->toDateString(),
                    'start_time' => Carbon::createFromTime($startHour)->format('H'),
                    'end_time' => Carbon::createFromTime($endHour)->format('H'),
                    'notes' => null,
                    'created_at' => now(),
                ]);
            }
        }
    }
}
