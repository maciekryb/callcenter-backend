<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\AgentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function __construct(protected AgentService $agentService) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agents,email',
            'queues' => 'required|array',
            'queues.*.name' => 'required|string|exists:queues,name',
            'queues.*.efficiency' => 'required|numeric|min:0|max:1',
        ]);

        $agent = $this->agentService->createAgentWithQueues($validated);

        return response()->json($agent, 201);
    }
}
