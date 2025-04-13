<?php

use App\Http\Controllers\BackgroundJobController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BackgroundJobController::class, 'index']);
Route::get('/{id}', [BackgroundJobController::class, 'show']);
Route::get('/view/logs', [BackgroundJobController::class, 'logs']);
Route::post('/{id}/retry', [BackgroundJobController::class, 'retry']);
Route::post('/{id}/cancel', [BackgroundJobController::class, 'cancel']);
