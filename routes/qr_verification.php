<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrVerificationController;

// Endpoint verifikasi QR Code dokumen surat
Route::get('/verifikasi-qr', [QrVerificationController::class, 'showForm'])->name('qr.verify.form');
Route::get('/verifikasi-qr/{token}', [QrVerificationController::class, 'verify'])->name('qr.verify');
Route::get('/qr-image/{token}', [QrVerificationController::class, 'image'])->name('qr.image');
