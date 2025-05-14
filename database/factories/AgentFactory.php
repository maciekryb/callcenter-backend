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
        $faker = \Faker\Factory::create('pl_PL');
        $fullName = $faker->firstName . ' ' . $faker->lastName;
        $fullName = mb_substr($fullName, 0, 20);

        return [
            'name' => $fullName,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }

    public function withQueues()
    {
        return $this->afterCreating(function (Agent $agent) {
            $queues = Queue::inRandomOrder()->take(rand(1, 2))->pluck('id');

            foreach ($queues as $queueId) {
                $agent->queues()->attach($queueId, [
                    'efficiency' => $this->faker->numberBetween(10, 20),
                ]);
            }
        });
    }
}
