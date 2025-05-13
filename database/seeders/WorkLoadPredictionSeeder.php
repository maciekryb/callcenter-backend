<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkLoadPredictionSeeder extends Seeder
{
    public function run(): void
    {
        $queues = DB::table('queues')->pluck('id');
        $today = now()->startOfWeek();

        foreach ($queues as $queueId) {
            for ($day = 0; $day < 6; $day++) {
                $date = $today->copy()->addDays($day)->toDateString();
                for ($hour = 0; $hour < 24; $hour++) {
                    // Więcej połączeń w godzinach dziennych (8:00-18:00), mniej w nocy
                    if ($hour >= 8 && $hour < 16) {
                        $calls = rand(15, 30); // Dzień: więcej połączeń
                    } elseif ($hour >= 16 && $hour < 20) {
                        $calls = rand(5, 15); // Wieczór: średnio
                    } else {
                        $calls = rand(0, 5); // Noc: mało połączeń
                    }
                    DB::table('work_load_predictions')->insert([
                        'queue_id' => $queueId,
                        'date' => $date,
                        'phone_calls_per_hour' => $calls,
                        'start_hour' => sprintf('%02d:00:00', $hour),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
