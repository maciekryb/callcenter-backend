<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AgentService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(protected AgentService $agentService) {}

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agents,email',
            'queues' => 'required|array',
            'queues.*.name' => 'required|string|exists:queues,name',
            'queues.*.efficiency' => 'required|numeric|min:1|max:100',
        ]);

        $agent = $this->agentService->createAgentWithQueues($validated);

        return response()->json($agent, 201);
    }
}
