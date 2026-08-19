<?php

use App\Http\Controllers\MpesaCallbackController;
use Illuminate\Support\Facades\Route;

Route::post('/mpesa/callback', MpesaCallbackController::class)
    ->middleware('throttle:60,1')
    ->name('mpesa.callback');
