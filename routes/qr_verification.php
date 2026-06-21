<?php

use App\Http\Controllers\QrVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/verifikasi-qr', [QrVerificationController::class, 'showForm'])->name('qr.verify.form');
Route::get('/verifikasi-qr/{token}', [QrVerificationController::class, 'verify'])->name('qr.verify');
Route::get('/qr-image/{token}', [QrVerificationController::class, 'image'])->name('qr.image');
