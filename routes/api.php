<?php

use App\Http\Controllers\AskController;
use Illuminate\Support\Facades\Route;

// Every call embeds the question and runs the agent, so it costs tokens: keep it throttled.
Route::post('/ask', AskController::class)->middleware('throttle:10,1');
