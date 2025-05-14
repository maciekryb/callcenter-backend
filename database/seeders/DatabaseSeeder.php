<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            QueueSeeder::class,
            WorkLoadPredictionSeeder::class,
        ]);
        Agent::factory()->withQueues()->count(20)->create();

        $this->call(AgentAvailabilitySeeder::class);
    }
}
