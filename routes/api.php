<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\AgentAvailabilityController; // Dodaj import kontrolera

Route::post('agents', [AgentController::class, 'create']);  // Tworzymy agenta

Route::get('queues', [QueueController::class, 'getQueues']); // Pobieramy wszystkie kolejki

Route::get('agent/schedule', [AgentAvailabilityController::class, 'getAllAgentsSchedule']); // Pobieramy grafik dla wszystkich agentów

Route::get('queue/{id}/agents-schedule', [AgentAvailabilityController::class, 'getAgentsScheduleByQueueId']); // Pobieramy grafik agentów dla danej kolejki po id