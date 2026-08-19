<?php

use App\Http\Controllers\AdminOpenPosController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterSupermarketController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PosSessionController;
use App\Http\Controllers\WorkspaceController;
use App\Livewire\Pos\CashierPos;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisterSupermarketController::class, 'create'])->name('register');
    Route::post('/register', [RegisterSupermarketController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/workspace', [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::post('/workspace', [WorkspaceController::class, 'store'])->name('workspace.store');

    Route::get('/onboarding', [OnboardingController::class, 'index'])->middleware('workspace')->name('onboarding.index');
    Route::post('/onboarding/business', [OnboardingController::class, 'business'])->middleware('workspace')->name('onboarding.business');
    Route::post('/onboarding/branch', [OnboardingController::class, 'branch'])->middleware('workspace')->name('onboarding.branch');
    Route::post('/onboarding/register', [OnboardingController::class, 'register'])->middleware('workspace')->name('onboarding.register');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->middleware('workspace')->name('onboarding.complete');

    Route::get('/open-pos', [AdminOpenPosController::class, 'index'])->middleware('workspace')->name('admin.open-pos');
    Route::post('/open-pos', [AdminOpenPosController::class, 'store'])->middleware('workspace')->name('admin.open-pos.store');

    Route::get('/pos/session', [PosSessionController::class, 'index'])->middleware('workspace')->name('pos.session');
    Route::post('/pos/session', [PosSessionController::class, 'store'])->middleware('workspace')->name('pos.session.store');
    Route::livewire('/pos', CashierPos::class)->middleware('workspace')->name('pos');
});
