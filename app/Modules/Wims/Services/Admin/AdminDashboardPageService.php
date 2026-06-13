<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\PendaftaranMagang;
use App\Models\PerusahaanMitra;
use App\Models\SuratPenetapan;
use Carbon\Carbon;

class AdminDashboardPageService
{
    public function build(): array
    {
        $pendingRegistrations = PendaftaranMagang::with(['mahasiswa', 'perusahaan'])
            ->where('status', 'pending')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (PendaftaranMagang $pendaftaran) => [
                'id' => $pendaftaran->id,
                'student_name' => $pendaftaran->mahasiswa?->name,
                'student_email' => $pendaftaran->mahasiswa?->email,
                'company_name' => $pendaftaran->perusahaan?->nama,
                'date_range' => $this->formatDateRange($pendaftaran->tanggal_mulai, $pendaftaran->tanggal_selesai),
                'status' => $pendaftaran->status,
            ])
            ->values();

        return [
            'summary' => [
                'active_students' => PendaftaranMagang::query()
                    ->where('status', 'aktif')
                    ->count(),
                'companies' => PerusahaanMitra::count(),
                'pending_registrations' => PendaftaranMagang::where('status', 'pending')->count(),
                'approved_registrations' => PendaftaranMagang::where('status', 'approved')->count(),
                'surat_requested' => SuratPenetapan::where('status', 'requested')->count(),
                'surat_generated' => SuratPenetapan::where('status', 'generated')->count(),
                'surat_failed' => SuratPenetapan::where('status', 'failed')->count(),
            ],
            'pendingRegistrations' => $pendingRegistrations->all(),
        ];
    }

    private function formatDateRange(mixed $startDate, mixed $endDate): string
    {
        return sprintf(
            '%s - %s',
            $this->formatDate($startDate),
            $this->formatDate($endDate),
        );
    }

    private function formatDate(mixed $date): string
    {
        if (blank($date)) {
            return '-';
        }

        if ($date instanceof Carbon) {
            return $date->translatedFormat('d M Y');
        }

        return Carbon::parse($date)->translatedFormat('d M Y');
    }
}
