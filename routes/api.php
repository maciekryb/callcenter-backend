<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\AgentAvailabilityController; // Dodaj import kontrolera
use App\Http\Controllers\WorkLoadPredictionController;

Route::post('agents', [AgentController::class, 'create']);  // Tworzymy agenta

Route::get('queues', [QueueController::class, 'getQueues']); // Pobieramy wszystkie kolejki

Route::get('queue/{id}/agents-schedule', [AgentAvailabilityController::class, 'getAgentsScheduleByQueueId']); // Pobieramy grafik agentów dla danej kolejki po id

Route::get('queue/{id}/work-load', [WorkLoadPredictionController::class, 'getWorkLoadPredictionByQueueId']); // Pobieramy prognozę obciążenia dla danej kolejki po id