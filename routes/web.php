<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackgroundJobController;

Route::get('/', [BackgroundJobController::class, 'index']);
Route::get('/{id}', [BackgroundJobController::class, 'show']);
Route::get('/{id}/retry', [BackgroundJobController::class, 'retry']);
Route::get('/{id}/cancel', [BackgroundJobController::class, 'cancel']);
Route::get('/view/logs', [BackgroundJobController::class, 'logs']);
