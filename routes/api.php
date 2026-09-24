<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/tickets', [TicketController::class, 'store']);

Route::get('/tickets', [TicketController::class, 'index']);

Route::get('/tickets/{id}', [TicketController::class, 'show']);

Route::patch('/tickets/{id}', [TicketController::class, 'update']);

Route::post('/tickets/{id}/classify', [TicketController::class, 'classify']);

Route::get('/stats', [TicketController::class, 'stats']);