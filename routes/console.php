<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('wims:sync-national-holidays')
    ->dailyAt('01:00')
    ->timezone(config('app.timezone', 'Asia/Jakarta'))
    ->description('Menyinkronkan tanggal merah dan libur nasional ke tabel hari_liburs.');

Schedule::command('wims:check-monitoring-alerts')
    ->dailyAt('18:00')
    ->timezone(config('app.timezone', 'Asia/Jakarta'))
    ->description('Memeriksa warning monitoring mahasiswa untuk dosen pembimbing setiap hari.');
