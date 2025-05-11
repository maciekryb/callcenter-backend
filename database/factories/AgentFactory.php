<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Queue;
use Illuminate\Database\Eloquent\Factories\Factory;

class AgentFactory extends Factory
{
    protected $model = Agent::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }

    public function withQueues()
    {
        return $this->afterCreating(function (Agent $agent) {
            $queues = Queue::inRandomOrder()->take(rand(1, 2))->pluck('id');

            foreach ($queues as $queueId) {
                $agent->queues()->attach($queueId, [
                    'efficiency' => $this->faker->randomFloat(2, 0.4, 1.0),
                ]);
            }
        });
    }
}
