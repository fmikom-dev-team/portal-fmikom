# Analisis Source Code WIMS

Tanggal analisis: 2026-08-01
Repository: `C:\laragon\www\portal-fmikom-integration`

## Identifikasi File WIMS

### Route dan bootstrap
- `routes/wims.php`
- `routes/web.php`
- `bootstrap/app.php`

### Middleware, context, Inertia, dan security
- `app/Http/Middleware/CheckActiveContext.php`
- `app/Http/Middleware/EnsureModuleAccess.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Middleware/SecurityHeaders.php`

### Backend WIMS
- Seluruh controller, request, service, dan support di `app/Modules/Wims/**`
- File yang paling menentukan alur presensi dan geofencing:
  - `app/Modules/Wims/Controllers/Mahasiswa/AttendanceController.php`
  - `app/Modules/Wims/Services/Mahasiswa/Attendance/AttendanceActionService.php`
  - `app/Modules/Wims/Services/Mahasiswa/Attendance/AttendanceAvailabilityService.php`
  - `app/Modules/Wims/Services/Mahasiswa/Attendance/AttendancePageService.php`
  - `app/Modules/Wims/Services/Shared/Attendance/AttendanceService.php`
  - `app/Modules/Wims/Services/Shared/Attendance/AttendanceSyncService.php`
  - `app/Modules/Wims/Services/Mahasiswa/Period/StudentPeriodResolverService.php`
- File penting lain sesuai proposal:
  - `app/Modules/Wims/Controllers/Mahasiswa/RegistrationController.php`
  - `app/Modules/Wims/Controllers/Mahasiswa/KetidakhadiranController.php`
  - `app/Modules/Wims/Controllers/Mahasiswa/LogbookController.php`
  - `app/Modules/Wims/Controllers/Mahasiswa/LaporanController.php`
  - `app/Modules/Wims/Controllers/Admin/RegistrationController.php`
  - `app/Modules/Wims/Controllers/Admin/PlacementController.php`
  - `app/Modules/Wims/Controllers/Admin/MonitoringController.php`
  - `app/Modules/Wims/Controllers/Admin/AssessmentRecapController.php`
  - `app/Modules/Wims/Controllers/Dosen/MonitoringController.php`
  - `app/Modules/Wims/Controllers/Dosen/PenilaianMahasiswaController.php`
  - `app/Modules/Wims/Controllers/Mitra/MonitoringController.php`
  - `app/Modules/Wims/Controllers/Mitra/KetidakhadiranController.php`
  - `app/Modules/Wims/Controllers/Mitra/LogbookController.php`
  - `app/Modules/Wims/Controllers/Mitra/PenilaianMahasiswaController.php`
  - `app/Modules/Wims/Services/Shared/Absence/KetidakhadiranService.php`
  - `app/Modules/Wims/Services/Shared/Monitoring/MonitoringRegistrationResolverService.php`
  - `app/Modules/Wims/Services/Shared/Monitoring/MonitoringDetailService.php`
  - `app/Modules/Wims/Services/Shared/Monitoring/MonitoringSummaryService.php`
  - `app/Modules/Wims/Services/Shared/Monitoring/MonitoringHistoryService.php`
  - `app/Modules/Wims/Services/Shared/Monitoring/MonitoringAlertService.php`
  - `app/Modules/Wims/Services/Shared/Placement/PlacementWorkflowService.php`
  - `app/Modules/Wims/Services/Shared/Assessment/AssessmentSubmissionService.php`
  - `app/Modules/Wims/Services/Shared/Assessment/AssessmentTemplateResolverService.php`
  - `app/Modules/Wims/Services/Dosen/LecturerAssessmentWorkflowService.php`
  - `app/Modules/Wims/Services/Mitra/MitraAccessService.php`
  - `app/Modules/Wims/Services/Shared/Portal/WimsModuleRoleService.php`

### Model dan relasi WIMS
- `app/Models/Magang/PendaftaranMagang.php`
- `app/Models/Magang/PerusahaanMitra.php`
- `app/Models/Magang/AbsensiMagang.php`
- `app/Models/Magang/KetidakhadiranMagang.php`
- `app/Models/Magang/LogbookMagang.php`
- `app/Models/Magang/LogbookPhoto.php`
- `app/Models/Magang/HariLibur.php`
- `app/Models/Magang/PembimbingLapangan.php`
- `app/Models/Magang/SuratPenetapan.php`
- `app/Models/Magang/FinalReportTemplate.php`
- `app/Models/Magang/AssessmentTemplate.php`
- `app/Models/Magang/AssessmentComponent.php`
- `app/Models/Magang/AssessmentSubmission.php`
- `app/Models/Magang/AssessmentScore.php`
- `app/Models/Magang/PenilaianMagang.php`
- `app/Models/Magang/LowonganInfo.php`

### Frontend WIMS dan Inertia
- Seluruh halaman di `resources/js/pages/Modules/Wims/**`
- Komponen terkait lokasi mitra di `resources/js/components/Modules/Wims/CompanyLocationPicker.vue`
- Helper route actions di `resources/js/actions/App/Modules/Wims/**`
- Bootstrap Inertia umum di `resources/js/app.ts`
- File paling krusial untuk presensi: `resources/js/pages/Modules/Wims/Mahasiswa/Presensi/Index.vue`

### Storage, file access, dan URL file
- `app/Support/WimsStorage.php`
- `app/Support/PublicStorageUrl.php`
- `app/Modules/WorkOs/Controllers/ImageProxyController.php`

### Migration basis data
- Seluruh migration WIMS di `database/migrations/magang/*.php`
- Migration template laporan akhir di root:
  - `database/migrations/2026_07_01_000000_create_final_report_templates_table.php`
  - `database/migrations/2026_07_04_000000_add_template_type_to_final_report_templates_table.php`
- Ada file disabled yang perlu dicatat:
  - `database/migrations/magang/2026_07_04_000000_add_template_type_to_final_report_templates_table.disabled`

### Test otomatis
- `tests/Feature/Wims/WimsPortalIntegrationTest.php`
- `tests/Feature/Wims/WimsSupportServicesTest.php`
- `tests/Feature/Wims/WimsSchemaTest.php`
- `tests/Feature/Wims/WimsRegistrationRuleTest.php`
- `tests/Feature/Wims/WimsOperationalModelsTest.php`
- `tests/Feature/Wims/WimsAssessmentWorkflowTest.php`
- `tests/Feature/Wims/WimsAssessmentModelsTest.php`
- `tests/Feature/Wims/WimsInertiaPageResolutionTest.php`
- `tests/Unit/Wims/AssessmentSummaryTest.php`

## A. Ringkasan Struktur WIMS

- Route: seluruh endpoint WIMS dipusatkan di `routes/wims.php`, dipisah per aktor mahasiswa, admin, dosen, dan mitra.
- Backend: alur bisnis utama berada di `app/Modules/Wims`, terutama controller, form validation, dan service per domain seperti attendance, absence, monitoring, placement, report, dan assessment.
- Frontend: halaman WIMS dirender dengan Inertia ke Vue pada `resources/js/pages/Modules/Wims/**`; halaman presensi memanggil Geolocation API dan kamera di `Mahasiswa/Presensi/Index.vue`.
- Basis data: data inti disimpan pada tabel `pendaftaran_magangs`, `perusahaan_mitras`, `absensi_magangs`, `ketidakhadiran_magangs`, `logbook_magangs`, `logbook_photos`, `assessment_*`, `final_report_templates`, `hari_liburs`, dan tabel pendukung penempatan.
- Otorisasi: akses dijaga oleh route middleware `module.context:wims,...`, validasi ulang role aktif di `CheckActiveContext`, dan pembatasan query pada service atau controller.
- Penyimpanan file: file WIMS disimpan melalui `WimsStorage` pada disk `local`, lalu diakses lewat helper URL bertanda tangan atau proxy file.
- Pengujian: test WIMS tersedia dan pada 2026-08-01 berhasil dijalankan `60 passed, 462 assertions`, tetapi belum ada test numerik khusus untuk hasil Haversine atau endpoint presensi geofencing.

## B. File yang Diberi Komentar

| No. | File | Baris atau Method | Alasan Bagian Ini Krusial |
| --: | ---- | ----------------- | ------------------------- |
| 1 | `routes/wims.php` | group route WIMS dan route absensi | Menjelaskan pintu masuk route dan pemisahan akses per role. |
| 2 | `app/Http/Middleware/CheckActiveContext.php` | `handle()` | Menjelaskan bahwa hak akses tidak berhenti pada penyembunyian menu. |
| 3 | `app/Http/Middleware/HandleInertiaRequests.php` | `share()`, `resolveWimsSelectedPeriodId()` | Menjelaskan hubungan Laravel, session, dan Inertia untuk periode aktif. |
| 4 | `app/Http/Middleware/SecurityHeaders.php` | `buildPermissionsPolicy()` | Menjelaskan pembatasan kamera dan geolokasi hanya pada route tertentu. |
| 5 | `app/Modules/Wims/Controllers/Mahasiswa/AttendanceController.php` | `store()`, `checkout()` | Menjelaskan re-query pendaftaran, validasi backend ulang, dan pencegahan presensi ganda. |
| 6 | `app/Modules/Wims/Services/Mahasiswa/Attendance/AttendanceActionService.php` | `validateLocation()`, `createCheckIn()`, `storePhoto()` | Menjelaskan sumber lokasi mitra, relasi presensi ke pendaftaran, dan penyimpanan foto. |
| 7 | `app/Modules/Wims/Services/Shared/Attendance/AttendanceService.php` | `calculateDistance()`, `validateLocation()` | Menjelaskan lokasi implementasi Haversine dan aturan `<= radius`. |
| 8 | `app/Modules/Wims/Services/Mahasiswa/Period/StudentPeriodResolverService.php` | `resolveSelectedRegistrationFromCollection()` | Menjelaskan prioritas pendaftaran aktif dan persistensi konteks periode. |
| 9 | `app/Modules/Wims/Services/Shared/Monitoring/MonitoringRegistrationResolverService.php` | `resolveByDate()`, `authorizedLecturerQuery()`, `authorizedCompanyQuery()` | Menjelaskan pembatasan data dosen dan mitra. |
| 10 | `app/Modules/Wims/Services/Shared/Absence/KetidakhadiranService.php` | `validateSubmission()`, `approve()` | Menjelaskan overlap, perlindungan data presensi nyata, dan transaksi DB. |
| 11 | `app/Modules/Wims/Services/Shared/Placement/PlacementWorkflowService.php` | `validatePlacementUpdate()` | Menjelaskan validasi role dosen aktif pada backend. |
| 12 | `app/Modules/Wims/Services/Shared/Assessment/AssessmentSubmissionService.php` | `saveSubmission()` | Menjelaskan validasi backend dan kalkulasi nilai berbobot. |
| 13 | `app/Modules/Wims/Services/Dosen/LecturerAssessmentWorkflowService.php` | `isAuthorized()` | Menjelaskan filtering dosen pembimbing. |
| 14 | `app/Modules/Wims/Services/Mitra/MitraAccessService.php` | `canReviewAbsence()` | Menjelaskan filtering mitra berdasarkan perusahaan. |
| 15 | `app/Support/WimsStorage.php` | konstanta disk dan `locate()` | Menjelaskan mengapa file WIMS tidak langsung publik dan bagaimana path dijaga. |
| 16 | `app/Modules/WorkOs/Controllers/ImageProxyController.php` | `serve()` | Menjelaskan proxy file bertanda tangan dan path terenkripsi. |
| 17 | `resources/js/pages/Modules/Wims/Mahasiswa/Presensi/Index.vue` | `canSubmit`, `checkout`, `getLocation`, `submit` | Menjelaskan Geolocation API, loading state, dan relasi Vue-Inertia-Laravel. |

## C. Alur Presensi Berbasis Geofencing

1. Pengguna membuka `/wims/absensi`; route mahasiswa memerlukan `auth`, `EnsureFirstTimeLoginComplete`, dan `module.context:wims,mahasiswa`.
2. `AttendanceController::index()` merender halaman Inertia `Modules/Wims/Mahasiswa/Presensi/Index` dengan data dari `AttendancePageService::build()`.
3. `AttendancePageService` mengambil pendaftaran mahasiswa melalui `StudentPeriodResolverService`, memilih periode aktif atau periode yang tersimpan di session, lalu mengirim lokasi kantor, radius, status presensi, dan URL file pendukung ke Vue.
4. Di `Index.vue`, frontend meminta koordinat melalui `navigator.geolocation.getCurrentPosition(...)` dan mengelola error untuk izin ditolak, lokasi tidak tersedia, timeout, serta akurasi GPS yang terlalu lemah.
5. Frontend menghitung estimasi jarak lokal dengan rumus yang sepadan agar pengguna segera tahu apakah masih di dalam radius, lalu menahan tombol bila lokasi belum valid atau foto belum ada.
6. Saat submit, Vue mengirim `pendaftaran_id`, `latitude`, `longitude`, dan `photo` sebagai `FormData` lewat Inertia ke `AttendanceController::store()` atau `checkout()`.
7. Backend memvalidasi ulang input, lalu memuat ulang `PendaftaranMagang` milik mahasiswa yang login beserta relasi `perusahaan`, sehingga pengguna tidak menentukan mitra secara bebas dari frontend.
8. `AttendanceAvailabilityService` memeriksa apakah PKL sudah diaktifkan admin, hari ini masih dalam periode aktif, bukan hari libur atau di luar hari kerja perusahaan, dan tidak tertutup oleh ketidakhadiran yang telah disetujui.
9. `AttendanceActionService::validateLocation()` mengambil `latitude`, `longitude`, dan `radius_valid_meter` dari `perusahaan` pada `pendaftaran` aktif, lalu memanggil `AttendanceService::validateLocation()`.
10. `AttendanceService::calculateDistance()` mengubah derajat ke radian, memakai `earthRadius = 6371000`, menghitung jarak Haversine dalam meter, kemudian `validateLocation()` menyatakan valid hanya jika `distance <= radius`.
11. Jika lokasi tidak valid, backend menolak presensi dengan pesan error dan nilai jarak hasil hitung backend.
12. Jika check-in sudah ada pada tanggal yang sama, `AttendanceController::store()` menolak presensi ganda melalui `hasCheckedInToday()`.
13. Jika valid, `AttendanceActionService::createCheckIn()` menyimpan row `absensi_magangs` yang terhubung ke `pendaftaran_id`, beserta koordinat masuk, jarak, status lokasi, path foto, IP, user agent, waktu, dan status hadir atau terlambat.
14. Untuk check-out, backend mencari row presensi hari ini melalui `findTodayAttendance()`, memastikan check-in sudah ada dan `timestamp_keluar` belum terisi, lalu menyimpan data keluar pada row yang sama.

## D. Pertanyaan Penguji Berdasarkan Source Code

1. Mengapa route WIMS dipisah per aktor di `routes/wims.php`? Jawab: agar middleware role dapat diterapkan sejak route sebelum controller berjalan.
2. Mengapa `CheckActiveContext` masih mengecek database walau ada session `active_role`? Jawab: karena session dianggap dapat stale; middleware memvalidasi assignment aktif ke DB dengan cache 5 menit.
3. Mengapa `AttendanceController::store()` memuat ulang `PendaftaranMagang` dengan `where('mahasiswa_id', $request->user()->id)`? Jawab: agar mahasiswa hanya bisa presensi memakai pendaftaran miliknya sendiri.
4. Mengapa lokasi mitra tidak diambil dari request frontend? Jawab: karena backend mengambil `perusahaan` dari relasi `pendaftaran` aktif.
5. Di mana implementasi Haversine utama berada? Jawab: di `app/Modules/Wims/Services/Shared/Attendance/AttendanceService.php`.
6. Mengapa ada `deg2rad()` pada `AttendanceService`? Jawab: karena fungsi trigonometri Haversine bekerja dalam satuan radian.
7. Mengapa memakai `6371000`? Jawab: itu jari-jari bumi dalam meter sehingga hasil jarak langsung bermeter.
8. Mengapa aturan validasi lokasi menggunakan `<=`? Jawab: karena titik yang tepat di batas radius tetap dinyatakan valid.
9. Apa yang terjadi jika latitude, longitude, atau radius perusahaan belum ada? Jawab: backend mengembalikan `is_valid = false`; controller presensi menolak dengan pesan lokasi perusahaan belum diatur.
10. Apakah backend memvalidasi rentang latitude mahasiswa `-90..90` dan longitude `-180..180` saat presensi? Jawab: tidak ditemukan; pada presensi hanya `required|numeric`.
11. Bagaimana sistem mencegah presensi ganda? Jawab: `hasCheckedInToday()` mengecek row `absensi_magangs` untuk `pendaftaran_id` dan `tanggal` hari ini.
12. Bagaimana check-out dihubungkan dengan check-in? Jawab: `checkout()` mencari row presensi hari ini lalu mengupdate row yang sama, bukan membuat row baru.
13. Bagaimana sistem menentukan hadir atau terlambat? Jawab: `AttendanceAvailabilityService::resolveStatus()` membandingkan jam check-in dengan `jam_masuk + toleransi_terlambat_menit` milik perusahaan.
14. Bagaimana hari libur dan hari kerja perusahaan memengaruhi presensi? Jawab: `AttendanceAvailabilityService` memanggil `PerusahaanMitra::worksOnDate()` dengan lookup `HariLibur`.
15. Bagaimana izin atau sakit memengaruhi presensi harian? Jawab: `KetidakhadiranService` dapat menyinkronkan row absensi otomatis berstatus `izin` atau `sakit` setelah approval mitra.
16. Mengapa `KetidakhadiranService` memakai transaksi saat approve atau reject? Jawab: supaya perubahan status dan sinkronisasi row absensi konsisten dalam satu unit kerja.
17. Bagaimana dosen hanya melihat mahasiswa bimbingannya? Jawab: `authorizedLecturerQuery()` menambah `where('dosen_pembimbing_id', $currentUser->id)`.
18. Bagaimana mitra hanya melihat mahasiswa perusahaan yang terhubung? Jawab: `authorizedCompanyQuery()` menambah `where('perusahaan_id', $company->id)` setelah `MitraAccessService::resolveCompany()`.
19. Bagaimana nilai penilaian dipastikan sesuai template aktif? Jawab: `AssessmentSubmissionService` memeriksa role template, daftar component id, duplikasi komponen, dan kelengkapan komponen pada status `submitted`.
20. Mengapa kalkulasi nilai akhir dilakukan di backend? Jawab: `weighted_score` dan `total_score` dihitung ulang dari bobot template pada server.
21. Bagaimana file foto presensi disimpan? Jawab: `AttendanceActionService::storePhoto()` menyimpan file melalui `WimsStorage::storeUploadedFileAs()` ke direktori `absensi/check-in` atau `absensi/check-out`.
22. Apakah file WIMS langsung disajikan dari disk publik? Jawab: tidak; `WimsStorage` memprioritaskan disk `local`, lalu akses file memakai URL bertanda tangan atau proxy.
23. Bagaimana frontend dan Laravel saling berkomunikasi pada presensi? Jawab: `AttendanceController::index()` merender Inertia page, Vue mengirim `form.post(...)`, lalu controller dan service backend memproses ulang.
24. Apakah ada deteksi fake GPS di source code? Jawab: tidak ditemukan implementasi khusus anti fake GPS.
25. Apakah ada test otomatis yang membuktikan angka Haversine tertentu benar? Jawab: tidak ditemukan test numerik khusus untuk `AttendanceService::calculateDistance()`.

## E. Temuan Kritis

### Sesuai dengan proposal
- Role-based access ada pada level route dan middleware, bukan hanya menu.
- Pendaftaran, verifikasi status, penempatan, mitra, presensi masuk atau keluar, ketidakhadiran, logbook, monitoring, laporan akhir, penilaian, dan rekap nilai memang ada di source.
- Geofencing dan Haversine benar-benar diimplementasikan pada backend.
- Foto bukti presensi, koordinat, jarak, status lokasi, waktu, dan path file disimpan pada data presensi.
- Dosen dan mitra dibatasi oleh query backend sesuai kewenangannya.

### Perlu dikonfirmasi
- Ada implementasi Haversine ganda: `AttendanceService` dan method model `PerusahaanMitra::distanceTo()` atau `isWithinRadius()`; alur presensi utama memakai `AttendanceService`.
- Model legacy `PenilaianMagang` masih ada, tetapi alur penilaian aktual sudah memakai `assessment_templates`, `assessment_submissions`, dan `assessment_scores`.
- Terdapat file migration disabled `database/migrations/magang/2026_07_04_000000_add_template_type_to_final_report_templates_table.disabled` di samping migration aktif pada root.

### Berpotensi dipertanyakan penguji
- Mengapa validasi latitude atau longitude presensi di backend hanya `numeric`, tidak membatasi rentang koordinat.
- Mengapa akurasi GPS hanya diperiksa di frontend, bukan diverifikasi ulang di backend.
- Mengapa tidak ada test otomatis yang langsung menguji formula Haversine dan keputusan `distance <= radius`.
- Mengapa tidak ada test feature yang langsung menembak endpoint `/wims/absensi` untuk skenario di dalam atau di luar radius.

### Tidak konsisten dengan proposal
- Klaim anti fake GPS tidak dapat dibuat; source code tidak menunjukkan deteksi fake location.
- Klaim verifikasi identitas dari foto juga tidak dapat dibuat; tidak ada face recognition.

### Risiko keamanan atau integritas data
- Koordinat perangkat tetap berasal dari device pengguna; backend hanya memvalidasi terhadap radius dan tidak membuktikan keaslian sensor GPS.
- Akurasi GPS dibatasi di Vue, tetapi tidak terlihat ada penyimpanan atau validasi angka akurasi di backend.
- `StudentAbsenceActionService::resolveActiveRegistration()` tidak men-scope user sendiri, walau controller yang memanggilnya sudah memvalidasi `pendaftaran_id` milik mahasiswa login.

### Kode tersedia tetapi belum memiliki test
- Tidak ditemukan test numerik khusus untuk `AttendanceService::calculateDistance()`.
- Tidak ditemukan test feature khusus untuk controller presensi check-in atau check-out berbasis geofence.
- Tidak ditemukan test khusus untuk penolakan koordinat di luar radius pada endpoint presensi.

### Fitur disebut dalam proposal tetapi tidak ditemukan dalam source code
- Deteksi fake GPS.
- Mekanisme backend yang membuktikan lokasi fisik perangkat lebih dari sekadar membandingkan koordinat yang dikirim.

### Bagian yang aman untuk didemonstrasikan
- Otorisasi route dan middleware `module.context`.
- Pemilihan pendaftaran aktif melalui session dan Inertia.
- Pengambilan lokasi mitra dari relasi pendaftaran aktif.
- Perhitungan Haversine di backend dan pembanding `<= radius`.
- Penyimpanan foto dan file WIMS ke disk `local`.
- Query monitoring dosen dan mitra yang membatasi data sesuai relasi.

### Bagian yang perlu dipahami lebih dalam
- Alur sinkronisasi absensi otomatis dari ketidakhadiran.
- Pemilihan template penilaian aktif dan perhitungan skor berbobot.
- Pemisahan alur model legacy vs alur WIMS terbaru.

### Bagian yang berpotensi menjadi pertanyaan sulit
- Alasan tidak adanya validasi rentang koordinat presensi di backend.
- Keterbatasan sistem terhadap fake GPS atau manipulasi koordinat perangkat.
- Alasan belum adanya test otomatis khusus geofencing dan Haversine numerik.

### Bagian yang hanya dibatasi pada frontend
- Tombol presensi dinonaktifkan jika lokasi belum valid.
- Validasi akurasi GPS perangkat.
- Pengendalian loading kamera dan lokasi.
- Pencegahan submit ganda lewat `form.processing`.

### Bagian yang belum memiliki validasi backend penuh
- Rentang latitude dan longitude presensi mahasiswa.
- Akurasi GPS perangkat.

### Bagian yang belum memiliki automated test
- Endpoint presensi check-in atau check-out berbasis radius.
- Hasil numerik fungsi Haversine.
- Penolakan lokasi di luar geofence pada controller presensi.

## F. Daftar File yang Harus Dibuka Saat Sidang

1. `routes/wims.php` | route group mahasiswa, admin, dosen, mitra | pemetaan endpoint dan role | Pertanyaan: bagaimana hak akses diterapkan selain lewat menu?
2. `app/Http/Middleware/CheckActiveContext.php` | `handle()` | validasi role dan modul aktif | Pertanyaan: mengapa masih cek DB walau ada session?
3. `app/Http/Middleware/HandleInertiaRequests.php` | `share()`, `resolveWimsSelectedPeriodId()` | bridging session ke Inertia | Pertanyaan: bagaimana periode PKL tetap konsisten antarhalaman?
4. `app/Modules/Wims/Controllers/Mahasiswa/AttendanceController.php` | `store()`, `checkout()` | alur request presensi dari HTTP ke service | Pertanyaan: bagaimana backend mencegah mahasiswa memilih mitra bebas?
5. `app/Modules/Wims/Services/Mahasiswa/Attendance/AttendanceActionService.php` | `validateLocation()`, `createCheckIn()` | pengambilan lokasi mitra, relasi presensi, simpan foto | Pertanyaan: data apa saja yang disimpan saat check-in?
6. `app/Modules/Wims/Services/Shared/Attendance/AttendanceService.php` | `calculateDistance()`, `validateLocation()` | implementasi Haversine | Pertanyaan: mengapa memakai `deg2rad`, `6371000`, dan `<=`?
7. `app/Modules/Wims/Services/Mahasiswa/Attendance/AttendanceAvailabilityService.php` | `resolveAvailability()` | validasi periode, hari kerja, dan ketidakhadiran | Pertanyaan: kapan presensi ditolak walau mahasiswa berhasil login?
8. `app/Models/Magang/PendaftaranMagang.php` | relasi dan helper status | hubungan mahasiswa, perusahaan, dosen, dan presensi | Pertanyaan: apa arti status `approved`, `aktif`, `selesai`?
9. `app/Models/Magang/PerusahaanMitra.php` | kolom koordinat, radius, jam kerja | sumber data geofence | Pertanyaan: dari mana radius dan lokasi mitra berasal?
10. `app/Models/Magang/AbsensiMagang.php` | fillable, casts, photo URL | penyimpanan koordinat, jarak, dan file | Pertanyaan: bagaimana foto dihubungkan ke presensi?
11. `database/migrations/magang/2026_03_29_181007_create_absensi_magangs_table.php` dan `database/migrations/magang/2026_06_14_100300_add_wims_fields_to_absensi_magangs_table.php` | schema absensi | struktur basis data presensi | Pertanyaan: mengapa kolom koordinat dipisah masuk dan keluar?
12. `app/Modules/Wims/Services/Shared/Monitoring/MonitoringRegistrationResolverService.php` | `authorizedLecturerQuery()`, `authorizedCompanyQuery()` | scope data monitoring | Pertanyaan: bagaimana dosen atau mitra dibatasi hanya ke mahasiswanya?
13. `app/Modules/Wims/Services/Shared/Absence/KetidakhadiranService.php` | `validateSubmission()`, `approve()` | overlap, hari kerja, transaksi, sinkronisasi absensi | Pertanyaan: apa yang terjadi ketika izin disetujui?
14. `resources/js/pages/Modules/Wims/Mahasiswa/Presensi/Index.vue` | `canSubmit`, `getLocation`, `submit`, `checkout` | Geolocation API, kamera, Inertia form | Pertanyaan: apa yang terjadi bila izin lokasi ditolak atau akurasi GPS buruk?
15. `tests/Feature/Wims/WimsPortalIntegrationTest.php` dan `tests/Feature/Wims/WimsSupportServicesTest.php` | test route, role, storage, report | bukti automated test yang ada | Pertanyaan: bagian mana yang sudah diuji dan mana yang belum?