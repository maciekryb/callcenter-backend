<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Queue;
use Illuminate\Support\Facades\DB;

class AgentService
{
    public function createAgentWithQueues(array $data): Agent
    {
        return DB::transaction(function () use ($data) {
            $agent = Agent::create($data['name'], $data['email']);

            $this->assignQueues($agent, $data['queues']);

            return $agent->load('queues');
        });
    }

    private function assignQueues(Agent $agent, array $queueData): void
    {
        $syncData = [];

        foreach ($queueData as $queue) {
            $queueModel = Queue::getByNameOrFail($queue['name']);
            $syncData[$queueModel->id] = ['efficiency' => $queue['efficiency']];
        }

        $agent->queues()->sync($syncData);
    }
}
