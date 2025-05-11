<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\QueueController;

Route::post('agents', [AgentController::class, 'create']);  // Tworzymy agenta

Route::get('queues', [QueueController::class, 'getQueues']); // Pobieramy wszystkie kolejki