<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Services\AgentService;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function __construct(protected AgentService $agentService) {}

    public function getQueues()
    {
        $queues = Queue::all();

        return response()->json($queues, 201);
    }
}
