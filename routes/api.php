<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgentController;


Route::post('agents', [AgentController::class, 'create']);  // Tworzymy agenta