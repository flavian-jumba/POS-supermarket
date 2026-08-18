<?php

use App\Livewire\Pos\CashierPos;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/pos', CashierPos::class)->name('pos');
