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
        $today = Carbon::today();

        foreach ($queues as $queueId) {
            for ($day = 0; $day < 6; $day++) {
                $date = $today->copy()->addDays($day)->toDateString();
                for ($hour = 0; $hour < 24; $hour++) {
                    DB::table('work_load_predictions')->insert([
                        'queue_id' => $queueId,
                        'date' => $date,
                        'phone_calls_per_hour' => rand(0, 30),
                        'start_hour' => sprintf('%02d:00:00', $hour),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
