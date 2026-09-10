<?php

use App\Http\Controllers\QrVerificationController;
use Illuminate\Support\Facades\Route;

// Route publik: penerima dokumen belum tentu punya akun Portal FMIKOM.
Route::get('/verifikasi-qr', [QrVerificationController::class, 'showForm'])->name('qr.verify.form');
// Token di URL dipakai sebagai lookup server-side ke surat yang terdaftar.
Route::get('/verifikasi-qr/{token}', [QrVerificationController::class, 'verify'])->name('qr.verify');
// QR hanya merender URL verifikasi agar validasi tetap terjadi di backend.
Route::get('/qr-image/{token}', [QrVerificationController::class, 'image'])->name('qr.image');
