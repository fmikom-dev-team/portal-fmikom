<?php

use App\Http\Controllers\Auth\DashboardRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(route('dashboard', absolute: false));
    }
    return redirect(route('login', absolute: false));
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardRedirectController::class)->name('dashboard');
    Route::get('redirect-dashboard', DashboardRedirectController::class)->name('redirect.dashboard');
});

require __DIR__ . '/fast.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/qr_verification.php';
