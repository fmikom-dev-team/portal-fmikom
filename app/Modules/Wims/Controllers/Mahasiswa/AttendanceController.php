<?php

namespace App\Modules\Wims\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Mahasiswa\Attendance\AttendanceActionService;
use App\Modules\Wims\Services\Mahasiswa\Attendance\AttendanceExportService;
use App\Modules\Wims\Services\Mahasiswa\Attendance\AttendancePageService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendancePageService $attendancePageService,
        protected AttendanceExportService $attendanceExportService,
        protected AttendanceActionService $attendanceActionService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Mahasiswa/Presensi/Index', [
            'attendance' => $this->attendancePageService->build($request->user()),
        ]);
    }

    public function downloadHistory(Request $request)
    {
        return $this->attendanceExportService->download(
            $request->user(),
            $request->string('scope')->toString(),
        );
    }

    public function store(Request $request)
    {
        // Endpoint check-in memvalidasi identitas pendaftaran, koordinat GPS, dan foto
        // sebelum mahasiswa diperbolehkan mengirim bukti kehadiran.
        $request->validate([
            'pendaftaran_id' => [
                'required',
                Rule::exists('pendaftaran_magangs', 'id')->where(
                    fn ($query) => $query->where('mahasiswa_id', $request->user()->id)
                ),
            ],
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|image|max:5120',
        ]);

        $pendaftaran = PendaftaranMagang::with('perusahaan')
            ->findOrFail($request->pendaftaran_id);

        // Presensi hanya sah untuk mahasiswa pemilik pendaftaran yang sudah berada pada fase PKL aktif.
        if ($pendaftaran->mahasiswa_id !== $request->user()->id || $pendaftaran->status !== 'aktif') {
            abort(403);
        }

        [$canAttendToday, $workdayMessage] = $this->attendanceActionService->validateCheckInAvailability($pendaftaran);

        if (! $canAttendToday) {
            return back()->withErrors([
                'absen' => $workdayMessage,
            ]);
        }

        $perusahaan = $pendaftaran->perusahaan;

        if (! $perusahaan || ! $perusahaan->latitude || ! $perusahaan->longitude) {
            return back()->withErrors([
                'location' => 'Lokasi perusahaan belum diatur oleh admin.',
            ]);
        }

        $result = $this->attendanceActionService->validateLocation(
            $pendaftaran,
            (float) $request->latitude,
            (float) $request->longitude,
        );

        // Check-in ditolak jika hasil geofencing menunjukkan posisi di luar radius perusahaan.
        if (! $result['is_valid']) {
            return back()->withErrors([
                'location' => 'Anda berada di luar radius area presensi yang ditentukan.',
            ])->with([
                'distance' => round($result['distance'], 2),
                'is_valid' => false,
            ]);
        }

        if ($this->attendanceActionService->hasCheckedInToday($pendaftaran)) {
            return back()->withErrors([
                'absen' => 'Anda sudah melakukan presensi hari ini.',
            ]);
        }

        $this->attendanceActionService->createCheckIn(
            $pendaftaran,
            (float) $request->latitude,
            (float) $request->longitude,
            $request->file('photo'),
            $request->ip(),
            $request->userAgent(),
            $result,
        );

        return back()->with([
            'success' => 'Presensi berhasil.',
            'distance' => round($result['distance'], 2),
            'is_valid' => $result['is_valid'],
        ]);
    }

    public function checkout(Request $request)
    {
        // Endpoint check-out memakai validasi lokasi yang sama agar bukti kehadiran
        // saat pulang tetap memenuhi aturan geofencing sistem.
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|image|max:5120',
        ]);

        $pendaftaran = PendaftaranMagang::with('perusahaan')
            ->forMahasiswa($request->user()->id)
            ->active()
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->firstOrFail();

        $absensi = $this->attendanceActionService->findTodayAttendance($pendaftaran);

        if (! $absensi) {
            return back()->withErrors([
                'absen' => 'Anda belum check-in hari ini.',
            ]);
        }

        if ($absensi->timestamp_keluar) {
            return back()->withErrors([
                'absen' => 'Anda sudah check-out hari ini.',
            ]);
        }

        [$canAttendToday, $workdayMessage] = $this->attendanceActionService->validateCheckOutAvailability($pendaftaran);

        if (! $canAttendToday) {
            return back()->withErrors([
                'absen' => $workdayMessage,
            ]);
        }

        $perusahaan = $pendaftaran->perusahaan;

        if (! $perusahaan || ! $perusahaan->latitude || ! $perusahaan->longitude) {
            return back()->withErrors([
                'location' => 'Lokasi perusahaan belum diatur oleh admin.',
            ]);
        }

        $result = $this->attendanceActionService->validateLocation(
            $pendaftaran,
            (float) $request->latitude,
            (float) $request->longitude,
        );

        // Check-out juga ditolak bila mahasiswa sudah keluar dari area geofence perusahaan.
        if (! $result['is_valid']) {
            return back()->withErrors([
                'location' => 'Check-out ditolak karena Anda berada di luar radius area presensi.',
            ])->with([
                'distance_keluar' => round($result['distance'], 2),
                'is_valid_keluar' => false,
            ]);
        }

        $this->attendanceActionService->completeCheckOut(
            $absensi,
            (float) $request->latitude,
            (float) $request->longitude,
            $request->file('photo'),
            $result,
        );

        return back()->with([
            'success' => 'Check-out berhasil',
            'distance_keluar' => round($result['distance'], 2),
            'is_valid_keluar' => $result['is_valid'],
        ]);
    }
}
