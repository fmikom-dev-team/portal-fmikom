# Lampiran Kode Inti WIMS untuk HKI

Dokumen ini disusun dalam format lampiran per berkas agar langsung sesuai untuk kebutuhan HKI.

## Lampiran B.1 Route Utama dan Hak Akses WIMS

**Nama berkas:**  
`routes/wims.php`

**Fungsi:**  
Mendaftarkan route utama WIMS dan membatasi akses berdasarkan middleware modul dan role pengguna.

```php
Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims'])
    ->prefix('wims')
    ->group(function () {
        Route::get('/', [WimsDashboardController::class, 'index'])
            ->name('module.wims.dashboard');
    });

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims,mahasiswa'])
    ->prefix('wims')
    ->name('wims.')
    ->group(function () {
        Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pendaftaran', [MahasiswaRegistrationController::class, 'index'])->name('registration');
        Route::post('/pendaftaran', [MahasiswaRegistrationController::class, 'store'])->name('registration.store');
        Route::get('/absensi', [AttendanceController::class, 'index'])->name('attendance');
        Route::post('/absensi', [AttendanceController::class, 'store'])->name('absensi.store');
        Route::get('/logbook', [LogbookController::class, 'index'])->name('logbook');
        Route::post('/logbook', [LogbookController::class, 'store'])->name('logbook.store');
    });

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims,super-admin,admin,admin-universitas,admin-akademik,prodi'])
    ->prefix('wims/admin')
    ->name('wims.admin.')
    ->group(function () {
        Route::get('/pendaftaran', [AdminRegistrationController::class, 'index'])->name('registrations.index');
        Route::patch('/pendaftaran/{pendaftaran}/status', [AdminRegistrationController::class, 'updateStatus'])->name('registrations.update-status');
        Route::get('/penempatan', [AdminPlacementController::class, 'index'])->name('placements.index');
        Route::put('/penempatan/{pendaftaran}', [AdminPlacementController::class, 'update'])->name('placements.update');
        Route::get('/monitoring', [AdminMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/rekap-nilai', [AdminAssessmentRecapController::class, 'index'])->name('assessment-recap.index');
    });
```

## Lampiran B.2 Permission WIMS

**Nama berkas:**  
`database/seeders/PermissionSeeder.php`

**Fungsi:**  
Mendefinisikan permission utama yang dipakai modul WIMS.

```php
['group' => 'wims', 'name' => 'View WIMS Module', 'slug' => 'wims:module.view'],
['group' => 'wims', 'name' => 'Manage Internship Operations', 'slug' => 'wims:content.manage'],
['group' => 'wims', 'name' => 'Publish Internship Decisions', 'slug' => 'wims:content.publish'],
['group' => 'wims', 'name' => 'Manage WIMS Users', 'slug' => 'wims:users.manage'],
```

## Lampiran B.3 Proses Pendaftaran Mahasiswa

**Nama berkas:**  
`app/Modules/Wims/Services/Mahasiswa/Registration/StudentRegistrationActionService.php`

**Fungsi:**  
Menyusun payload pendaftaran, memeriksa apakah mahasiswa masih memiliki pendaftaran aktif, lalu menyimpan proposal PKL dan data pengajuan baru.

```php
public function buildPayload(array $input): array
{
    return [
        'tanggal_mulai' => $input['tanggal_mulai'] ?? null,
        'tanggal_selesai' => $input['tanggal_selesai'] ?? null,
        'perusahaan_diminati_nama' => $this->nullIfBlank($input['perusahaan_diminati_nama'] ?? null),
        'perusahaan_diminati_alamat' => $this->nullIfBlank($input['perusahaan_diminati_alamat'] ?? null),
        'catatan_pengajuan' => $this->nullIfBlank($input['catatan_pengajuan'] ?? null),
        'catatan_revisi_admin' => null,
        'perusahaan_id' => null,
        'dosen_pembimbing_id' => null,
        'status' => 'pending',
    ];
}

public function create(User $user, array $payload, UploadedFile $proposalFile): void
{
    $newPath = $this->proposalAttachmentService->store($proposalFile);

    DB::transaction(function () use ($user, $payload, $proposalFile, $newPath): void {
        $latestRegistration = PendaftaranMagang::where('mahasiswa_id', $user->id)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        if ($latestRegistration && ! in_array($latestRegistration->status, ['revisi', 'rejected', 'selesai'], true)) {
            throw ValidationException::withMessages([
                'registration' => 'Pendaftaran aktif atau pending sudah ada.',
            ]);
        }

        PendaftaranMagang::create([
            'mahasiswa_id' => $user->id,
            ...$payload,
            'proposal_pkl_path' => $newPath,
            'proposal_pkl_original_name' => $proposalFile->getClientOriginalName(),
            'proposal_pkl_uploaded_at' => now(),
        ]);
    });
}
```

## Lampiran B.4 Proses Persetujuan atau Revisi Pendaftaran

**Nama berkas:**  
`app/Modules/Wims/Services/Admin/AdminRegistrationActionService.php`

**Fungsi:**  
Mengubah status pendaftaran mahasiswa menjadi disetujui, revisi, atau ditolak beserta catatan admin.

```php
public function updateStatus(PendaftaranMagang $pendaftaran, array $validated): void
{
    $pendaftaran->update([
        'status' => $validated['status'],
        'catatan_revisi_admin' => $validated['status'] === 'revisi'
            ? trim((string) ($validated['catatan_revisi_admin'] ?? ''))
            : null,
    ]);
}
```

## Lampiran B.5 Proses Penempatan Mahasiswa

**Nama berkas:**  
`app/Modules/Wims/Services/Shared/Placement/PlacementActionService.php`

**Fungsi:**  
Menetapkan perusahaan mitra, dosen pembimbing, mengaktifkan masa magang, dan menandai pendaftaran selesai.

```php
public function updatePlacement(PendaftaranMagang $pendaftaran, array $validated): void
{
    $pendaftaran->update([
        'perusahaan_id' => $validated['perusahaan_id'],
        'dosen_pembimbing_id' => $validated['dosen_pembimbing_id'],
    ]);
}

public function activate(PendaftaranMagang $pendaftaran): void
{
    $pendaftaran->update([
        'status' => 'aktif',
    ]);
}

public function complete(PendaftaranMagang $pendaftaran): void
{
    DB::transaction(function () use ($pendaftaran): void {
        $this->markRegistrationsComplete(collect([$pendaftaran->loadMissing('mahasiswa')]));
    });
}
```

## Lampiran B.6 Relasi Data Penempatan

**Nama berkas:**  
`app/Models/Magang/PendaftaranMagang.php`

**Fungsi:**  
Menghubungkan data pendaftaran dengan perusahaan mitra dan dosen pembimbing.

```php
public function perusahaan(): BelongsTo
{
    return $this->belongsTo(PerusahaanMitra::class, 'perusahaan_id');
}

public function dosenPembimbing(): BelongsTo
{
    return $this->belongsTo(User::class, 'dosen_pembimbing_id');
}
```

## Lampiran B.7 Relasi Mitra Perusahaan

**Nama berkas:**  
`app/Models/Magang/PerusahaanMitra.php`

**Fungsi:**  
Menghubungkan perusahaan mitra dengan akun mitra dan daftar pendaftaran mahasiswa yang ditempatkan.

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}

public function pendaftaranMagangs(): HasMany
{
    return $this->hasMany(PendaftaranMagang::class, 'perusahaan_id');
}
```

## Lampiran B.8 Proses Validasi Radius Presensi

**Nama berkas:**  
`app/Modules/Wims/Services/Shared/Attendance/AttendanceService.php`

**Fungsi:**  
Menghitung jarak pengguna dari lokasi magang dengan rumus Haversine dan menentukan apakah presensi dapat diterima berdasarkan radius yang ditetapkan.

```php
public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $earthRadius = 6371000;

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat / 2) * sin($dLat / 2)
        + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
        * sin($dLng / 2) * sin($dLng / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}

public function validateLocation(
    float $userLat,
    float $userLng,
    ?float $officeLat,
    ?float $officeLng,
    ?float $radius,
): array {
    if ($officeLat === null || $officeLng === null || $radius === null || $radius <= 0) {
        return ['distance' => null, 'is_valid' => false];
    }

    $distance = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);

    return [
        'distance' => round($distance, 2),
        'is_valid' => $distance <= $radius,
    ];
}
```

## Lampiran B.9 Penyimpanan Presensi Berbasis Lokasi

**Nama berkas:**  
`app/Modules/Wims/Services/Mahasiswa/Attendance/AttendanceActionService.php`

**Fungsi:**  
Memvalidasi lokasi mahasiswa terhadap lokasi perusahaan dan menyimpan data presensi masuk yang berisi koordinat, jarak, status valid, foto, IP address, dan user agent.

```php
public function validateLocation(PendaftaranMagang $pendaftaran, float $latitude, float $longitude): array
{
    $perusahaan = $pendaftaran->perusahaan;

    return $this->attendanceService->validateLocation(
        $latitude,
        $longitude,
        $perusahaan->latitude,
        $perusahaan->longitude,
        $perusahaan->radius_valid_meter
    );
}

public function createCheckIn(
    PendaftaranMagang $pendaftaran,
    float $latitude,
    float $longitude,
    ?UploadedFile $photo,
    string $ipAddress,
    ?string $userAgent,
    array $locationResult,
    ?CarbonInterface $checkedAt = null,
): AbsensiMagang {
    $checkedAt ??= now();
    $photoPath = $this->storePhoto($photo, 'absensi/check-in');

    return AbsensiMagang::create([
        'pendaftaran_id' => $pendaftaran->id,
        'tanggal' => $checkedAt->toDateString(),
        'waktu_masuk' => $checkedAt->format('H:i:s'),
        'timestamp_masuk' => $checkedAt,
        'latitude_masuk' => $latitude,
        'longitude_masuk' => $longitude,
        'distance_masuk' => $locationResult['distance'],
        'lokasi_valid' => $locationResult['is_valid'],
        'foto_bukti_path' => $photoPath,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
    ]);
}
```

## Lampiran B.10 Struktur Tabel Presensi

**Nama berkas:**  
`database/migrations/magang/2026_03_29_181007_create_absensi_magangs_table.php`  
`database/migrations/magang/2026_06_14_100300_add_wims_fields_to_absensi_magangs_table.php`

**Fungsi:**  
Mendefinisikan struktur tabel presensi yang menyimpan waktu masuk dan keluar, koordinat GPS, jarak hasil validasi radius, serta bukti presensi.

```php
$table->date('tanggal');
$table->time('waktu_masuk')->nullable();
$table->time('waktu_keluar')->nullable();
$table->decimal('latitude_masuk', 10, 8)->nullable();
$table->decimal('longitude_masuk', 11, 8)->nullable();
$table->decimal('latitude_keluar', 10, 8)->nullable();
$table->decimal('longitude_keluar', 11, 8)->nullable();
$table->boolean('lokasi_valid')->default(false);
$table->string('foto_bukti_path')->nullable();
$table->string('status')->nullable();
$table->text('keterangan')->nullable();

$table->timestamp('timestamp_masuk')->nullable();
$table->timestamp('timestamp_keluar')->nullable();
$table->decimal('distance_masuk', 10, 2)->nullable();
$table->decimal('distance_keluar', 10, 2)->nullable();
$table->string('foto_bukti_checkout_path')->nullable();
$table->string('ip_address', 45)->nullable();
$table->text('user_agent')->nullable();
```

## Lampiran B.11 Penyimpanan Logbook Harian

**Nama berkas:**  
`app/Modules/Wims/Services/Mahasiswa/Logbook/LogbookActionService.php`

**Fungsi:**  
Menyimpan logbook harian mahasiswa beserta jam kerja, aktivitas, kompetensi, status review, dan lampiran foto kegiatan.

```php
public function create(PendaftaranMagang $pendaftaran, array $attributes, array $photos = []): void
{
    DB::transaction(function () use ($pendaftaran, $attributes, $photos, &$storedPaths): void {
        $logbook = LogbookMagang::create([
            'pendaftaran_id' => $pendaftaran->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => $attributes['jam_mulai'],
            'jam_selesai' => $attributes['jam_selesai'],
            'aktivitas_harian' => $attributes['aktivitas_harian'],
            'kompetensi_dicapai' => $attributes['kompetensi_dicapai'],
            'status' => 'pending',
        ]);

        foreach ($photos as $photo) {
            $path = $this->storePhoto($photo);

            LogbookPhoto::create([
                'logbook_id' => $logbook->id,
                'file_path' => $path,
            ]);
        }
    });
}
```

## Lampiran B.12 Pengajuan Ketidakhadiran Mahasiswa

**Nama berkas:**  
`app/Modules/Wims/Services/Mahasiswa/Absence/StudentAbsenceActionService.php`

**Fungsi:**  
Menyimpan pengajuan izin atau sakit mahasiswa dengan rentang tanggal, alasan, dan lampiran bukti.

```php
public function submit(PendaftaranMagang $pendaftaran, int $mahasiswaId, array $validated, ?UploadedFile $bukti): void
{
    $resolved = $this->ketidakhadiranService->validateSubmission($pendaftaran, $validated);
    $buktiPath = $this->storeProof($bukti);

    KetidakhadiranMagang::query()->create([
        'pendaftaran_id' => $pendaftaran->id,
        'mahasiswa_id' => $mahasiswaId,
        'perusahaan_id' => $pendaftaran->perusahaan_id,
        'tanggal_mulai' => $resolved['start_date']->toDateString(),
        'tanggal_selesai' => $resolved['end_date']->toDateString(),
        'jenis' => $validated['jenis'],
        'alasan' => $validated['alasan'],
        'bukti_path' => $buktiPath,
        'status' => 'pending',
        'submitted_at' => now(),
    ]);
}
```

## Lampiran B.13 Keputusan Ketidakhadiran

**Nama berkas:**  
`app/Modules/Wims/Services/Shared/Absence/KetidakhadiranService.php`

**Fungsi:**  
Memproses persetujuan atau penolakan pengajuan ketidakhadiran oleh mitra serta melakukan sinkronisasi data presensi.

```php
public function approve(KetidakhadiranMagang $ketidakhadiran, User $reviewer, ?string $catatanMitra = null): void
{
    DB::transaction(function () use ($ketidakhadiran, $reviewer, $catatanMitra): void {
        $ketidakhadiran->update([
            'status' => 'approved',
            'reviewed_by_mitra_user_id' => $reviewer->id,
            'reviewed_by_mitra_at' => now(),
            'catatan_mitra' => $catatanMitra,
        ]);

        $this->syncApprovedAttendanceRows($ketidakhadiran);
    });
}

public function reject(KetidakhadiranMagang $ketidakhadiran, User $reviewer, ?string $catatanMitra = null): void
{
    DB::transaction(function () use ($ketidakhadiran, $reviewer, $catatanMitra): void {
        $ketidakhadiran->update([
            'status' => 'rejected',
            'reviewed_by_mitra_user_id' => $reviewer->id,
            'reviewed_by_mitra_at' => now(),
            'catatan_mitra' => $catatanMitra,
        ]);
    });
}
```

## Lampiran B.14 Pengambilan Data Monitoring

**Nama berkas:**  
`app/Modules/Wims/Services/Shared/Monitoring/MonitoringDetailService.php`

**Fungsi:**  
Menyusun payload monitoring yang berisi data mahasiswa, riwayat presensi, riwayat logbook, ringkasan, dan kesiapan penilaian.

```php
$payload = [
    'student' => [
        'id' => $pendaftaran->mahasiswa?->id,
        'name' => $pendaftaran->mahasiswa?->name,
        'company' => [
            'id' => $pendaftaran->perusahaan?->id,
            'name' => $pendaftaran->perusahaan?->nama,
        ],
        'pendaftaran_id' => $pendaftaran->id,
        'status_pendaftaran' => $pendaftaran->status,
        'is_ready_for_assessment' => $pendaftaran->isReadyForAssessment(),
        'period_start' => $periodStart,
        'period_end' => $periodEnd,
    ],
    'history' => [
        'attendance' => $attendanceHistory,
        'logbook' => $logbookHistory,
    ],
    'assessment' => $assessment,
    'summary' => [
        'attendance' => $attendanceSummary,
        'logbook' => $logbookSummary,
    ],
];
```

## Lampiran B.15 Penyimpanan Nilai Dosen dan Mitra

**Nama berkas:**  
`app/Modules/Wims/Services/Shared/Assessment/AssessmentSubmissionService.php`

**Fungsi:**  
Menyimpan nilai per komponen, menghitung bobot nilai, dan membentuk total nilai akhir dari dosen atau mitra.

```php
public function saveSubmission(
    PendaftaranMagang $pendaftaran,
    AssessmentTemplate $template,
    User $user,
    string $role,
    ?AssessmentSubmission $existingSubmission,
    array $validated,
): void {
    $status = $validated['action'] === 'submitted' ? 'submitted' : 'draft';

    DB::transaction(function () use (
        $existingSubmission,
        $pendaftaran,
        $role,
        $scoresByComponent,
        $template,
        $user,
        $validated,
        $status
    ): void {
        $submission = AssessmentSubmission::query()->updateOrCreate(
            [
                'pendaftaran_magang_id' => $pendaftaran->id,
                'assessment_template_id' => $template->id,
                'assessor_id' => $user->id,
                'assessor_role' => $role,
            ],
            [
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
                'submitted_at' => $status === 'submitted' ? now() : $existingSubmission?->submitted_at,
            ],
        );

        $totalScore = 0;

        foreach ($scoresByComponent as $componentId => $scorePayload) {
            $component = $template->components->firstWhere('id', (int) $componentId);
            $score = round((float) $scorePayload['score'], 2);
            $weightedScore = round($score * ((float) $component->weight_percentage / 100), 2);
            $totalScore += $weightedScore;

            $submission->scores()->updateOrCreate(
                ['assessment_component_id' => $component->id],
                [
                    'score' => $score,
                    'weighted_score' => $weightedScore,
                    'note' => $scorePayload['note'] ?? null,
                ],
            );
        }

        $submission->update([
            'total_score' => round($totalScore, 2),
        ]);
    });
}
```

## Lampiran B.16 Struktur Tabel Pendaftaran Magang

**Nama berkas:**  
`database/migrations/magang/2026_03_29_181005_create_pendaftaran_magangs_table.php`  
`database/migrations/magang/2026_06_14_100100_add_wims_fields_to_pendaftaran_magangs_table.php`

**Fungsi:**  
Mendefinisikan struktur data utama pendaftaran magang, termasuk relasi mahasiswa, perusahaan, dosen pembimbing, dan lampiran proposal atau laporan akhir.

```php
$table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
$table->foreignId('perusahaan_id')->constrained('perusahaan_mitras')->cascadeOnDelete();
$table->foreignId('dosen_pembimbing_id')->nullable()->constrained('users')->nullOnDelete();
$table->date('tanggal_mulai');
$table->date('tanggal_selesai');
$table->string('status')->default('pending');

$table->string('perusahaan_diminati_nama')->nullable();
$table->text('perusahaan_diminati_alamat')->nullable();
$table->text('catatan_pengajuan')->nullable();
$table->text('catatan_revisi_admin')->nullable();
$table->string('laporan_akhir_path')->nullable();
```

## Lampiran B.17 Struktur Tabel Ketidakhadiran dan Penilaian

**Nama berkas:**  
`database/migrations/magang/2026_06_14_100600_create_ketidakhadiran_magangs_table.php`  
`database/migrations/magang/2026_06_14_101100_create_assessment_submissions_table.php`

**Fungsi:**  
Mendefinisikan struktur data pengajuan ketidakhadiran serta struktur penyimpanan hasil penilaian dosen dan mitra.

```php
$table->foreignId('pendaftaran_id')->constrained('pendaftaran_magangs')->restrictOnDelete();
$table->foreignId('mahasiswa_id')->constrained('users')->restrictOnDelete();
$table->foreignId('perusahaan_id')->constrained('perusahaan_mitras')->restrictOnDelete();
$table->date('tanggal_mulai');
$table->date('tanggal_selesai');
$table->string('jenis', 30);
$table->text('alasan');
$table->string('status', 20)->default('pending');
$table->foreignId('reviewed_by_mitra_user_id')->nullable()->constrained('users')->nullOnDelete();

$table->foreignId('pendaftaran_magang_id')->constrained('pendaftaran_magangs')->restrictOnDelete();
$table->foreignId('assessment_template_id')->constrained('assessment_templates')->restrictOnDelete();
$table->foreignId('assessor_id')->constrained('users')->restrictOnDelete();
$table->string('assessor_role', 32);
$table->decimal('total_score', 8, 2)->nullable();
$table->string('status', 32)->default('draft');
$table->timestamp('submitted_at')->nullable();
```

## Lampiran B.18 Model Utama WIMS

**Nama berkas:**  
`app/Models/Magang/PendaftaranMagang.php`

**Fungsi:**  
Menjadi model inti WIMS yang menghubungkan data mahasiswa, perusahaan, dosen pembimbing, presensi, logbook, dan penilaian.

```php
public function mahasiswa(): BelongsTo
{
    return $this->belongsTo(User::class, 'mahasiswa_id');
}

public function perusahaan(): BelongsTo
{
    return $this->belongsTo(PerusahaanMitra::class, 'perusahaan_id');
}

public function dosenPembimbing(): BelongsTo
{
    return $this->belongsTo(User::class, 'dosen_pembimbing_id');
}

public function absensis(): HasMany
{
    return $this->hasMany(AbsensiMagang::class, 'pendaftaran_id');
}

public function logbooks(): HasMany
{
    return $this->hasMany(LogbookMagang::class, 'pendaftaran_id');
}

public function assessmentSubmissions(): HasMany
{
    return $this->hasMany(AssessmentSubmission::class, 'pendaftaran_magang_id');
}
```

## Lampiran B.19 Frontend Halaman Presensi

**Nama berkas:**  
`resources/js/pages/Modules/Wims/Mahasiswa/Presensi/Index.vue`

**Fungsi:**  
Menangani input presensi mahasiswa berupa koordinat lokasi, foto verifikasi, validasi radius, dan pengiriman data presensi ke backend.

```ts
const form = useForm({
    pendaftaran_id: null as number | null,
    latitude: null as number | null,
    longitude: null as number | null,
    photo: null as File | null,
});

const calculateDistanceInMeters = (
    lat1: number,
    lng1: number,
    lat2: number,
    lng2: number,
) => {
    const earthRadius = 6371000;
    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLng = ((lng2 - lng1) * Math.PI) / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLng / 2) *
            Math.sin(dLng / 2);

    return earthRadius * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
};

form.post(absensiRoutes.store.url(), {
    forceFormData: true,
});
```

## Lampiran B.20 Frontend Halaman Pendaftaran

**Nama berkas:**  
`resources/js/pages/Modules/Wims/Mahasiswa/Pendaftaran/Index.vue`

**Fungsi:**  
Menangani form pengajuan pendaftaran magang dan unggah proposal dari sisi mahasiswa.

```ts
const form = useForm({
    tanggal_mulai: props.formDefaults.tanggal_mulai ?? '',
    tanggal_selesai: props.formDefaults.tanggal_selesai ?? '',
    perusahaan_diminati_nama: props.formDefaults.perusahaan_diminati_nama ?? '',
    perusahaan_diminati_alamat: props.formDefaults.perusahaan_diminati_alamat ?? '',
    catatan_pengajuan: props.formDefaults.catatan_pengajuan ?? '',
    proposal_pkl: null as File | null,
});
```

## Lampiran B.21 Frontend Halaman Logbook

**Nama berkas:**  
`resources/js/pages/Modules/Wims/Mahasiswa/Logbook/Index.vue`

**Fungsi:**  
Menangani input logbook harian berupa jam kerja, aktivitas, kompetensi, dan foto kegiatan.

```ts
const form = useForm({
    jam_mulai: '',
    jam_selesai: '',
    aktivitas_harian: '',
    kompetensi_dicapai: '',
    photos: [] as File[],
});
```

## Lampiran B.22 Frontend Halaman Monitoring

**Nama berkas:**  
`resources/js/pages/Modules/Wims/Admin/Monitoring/Show.vue`

**Fungsi:**  
Menampilkan riwayat presensi, riwayat logbook, ringkasan monitoring, dan tautan unduhan data monitoring.

```ts
const attendanceHistory = computed(() => props.history.attendance ?? []);
const logbookHistory = computed(() => props.history.logbook ?? []);
const summary = computed(() => props.summary ?? {});
const assessment = computed(() => props.assessment ?? {});

const attendanceDownloadUrl = computed(() => {
    if (!props.student.pendaftaran_id) {
        return '#';
    }

    return `/wims/admin/monitoring/${props.student.pendaftaran_id}/download/absensi`;
});
```

## Lampiran B.23 Frontend Halaman Penilaian

**Nama berkas:**  
`resources/js/pages/Modules/Wims/Dosen/PenilaianMahasiswa/Show.vue`

**Fungsi:**  
Menangani input nilai per komponen, perhitungan bobot nilai, dan pengiriman hasil penilaian dosen.

```ts
const form = useForm({
    scores: (props.template?.components ?? []).map((component) => ({
        component_id: component.id,
        score: component.score ?? null,
        note: component.note ?? '',
    })),
    notes: props.submission?.notes ?? '',
    action: 'draft',
});

const totalWeightedScore = computed(() =>
    form.scores.reduce((total, item) => {
        const component = props.template?.components.find((entry) => entry.id === item.component_id);
        const scoreValue = Number(item.score ?? 0);
        const weight = Number(component?.weight_percentage ?? 0);

        return total + (Number.isFinite(scoreValue) ? scoreValue * (weight / 100) : 0);
    }, 0),
);

form.post(`/wims/dosen/penilaian-mahasiswa/${props.student.pendaftaran_id}`, {
    preserveScroll: true,
});
```
