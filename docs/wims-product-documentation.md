# WIMS Product Documentation

Dokumen ini menjelaskan WIMS berdasarkan state repo lokal saat ini. Isinya disusun dari implementasi yang sudah ada di branch/PR lokal dan belum dianggap sebagai rilis final sampai PR tersebut di-merge.

## 1. Ringkasan Produk

WIMS adalah **Web-based Internship Management System** untuk pengelolaan PKL dan magang FMIKOM.

Tujuan produk:
- memusatkan alur pendaftaran magang
- mengelola penempatan mahasiswa ke mitra
- mencatat presensi, logbook, dan ketidakhadiran
- memfasilitasi monitoring oleh dosen dan mitra
- menyediakan penilaian dan rekap hasil
- menyimpan template laporan akhir dan dokumen pendukung

## 2. Status Dokumentasi

Status saat ini:
- WIMS sudah terdaftar sebagai modul aktif di portal lokal
- struktur route, controller, service, dan halaman Inertia untuk WIMS sudah tersedia di repo
- dokumentasi ini masih merupakan snapshot lokal, karena ada PR WIMS yang belum di-merge

Implikasi:
- isi fitur di dokumen ini harus dianggap mengikuti kode lokal, bukan referensi final dari branch utama
- jika PR berubah sebelum merge, dokumen perlu diperbarui agar tetap sinkron

## 3. Ruang Lingkup Produk

WIMS mendukung 4 peran utama:
- mahasiswa
- dosen
- mitra
- admin

Admin di repo ini mencakup beberapa sub-role portal:
- super-admin
- admin
- admin-universitas
- admin-akademik
- prodi

## 4. Nilai Bisnis

WIMS dipakai untuk:
- mengurangi proses manual dalam pengelolaan magang
- menjaga satu sumber data untuk status pendaftaran, penempatan, presensi, dan evaluasi
- memberikan visibilitas ke semua pihak yang terlibat dalam proses magang
- menyatukan administrasi akademik dengan aktivitas lapangan

## 5. Fitur Utama

### Untuk Mahasiswa
- dashboard mahasiswa
- profil mahasiswa
- pendaftaran magang
- unduh template proposal
- absensi masuk dan checkout
- unggah dan kelola logbook
- pengajuan ketidakhadiran
- pengelolaan laporan akhir
- unduh template laporan dan laporan final

### Untuk Dosen
- dashboard dosen
- monitoring mahasiswa
- detail monitoring per mahasiswa
- penilaian mahasiswa
- lihat dan unduh laporan akhir mahasiswa

### Untuk Mitra
- dashboard mitra
- monitoring mahasiswa
- detail monitoring per mahasiswa
- review logbook
- persetujuan atau penolakan ketidakhadiran
- penilaian mahasiswa
- lihat dan unduh laporan akhir mahasiswa

### Untuk Admin
- dashboard admin WIMS
- manajemen perusahaan mitra
- aktivasi akun perusahaan
- verifikasi dan pengelolaan pendaftaran
- penempatan mahasiswa ke mitra
- monitoring keseluruhan
- rekap nilai dosen dan mitra
- template laporan akhir
- template penilaian

## 6. Alur Produk

### 6.1 Alur Mahasiswa
1. login ke portal
2. masuk ke modul WIMS
3. lengkapi profil
4. daftar magang
5. menunggu verifikasi dan penempatan
6. melakukan absensi dan isi logbook selama periode magang
7. mengajukan ketidakhadiran bila diperlukan
8. unggah laporan akhir
9. mengunduh dan memeriksa hasil akhir jika tersedia

### 6.2 Alur Admin
1. memeriksa pendaftaran mahasiswa
2. menyetujui atau menolak pendaftaran
3. mengelola perusahaan mitra
4. melakukan penempatan mahasiswa
5. memonitor progress magang
6. menyiapkan template penilaian dan template laporan
7. mengunduh rekap nilai

### 6.3 Alur Dosen dan Mitra
1. masuk ke dashboard sesuai peran
2. membuka daftar mahasiswa yang dibimbing atau ditempatkan
3. meninjau monitoring dan logbook
4. memberi penilaian
5. melihat laporan akhir mahasiswa

## 7. Struktur Implementasi Lokal

Lokasi implementasi yang sudah ada di repo:
- backend modul: `app/Modules/Wims/`
- route modul: `routes/wims.php`
- frontend modul: `resources/js/pages/Modules/Wims/`
- support dan service shared: `app/Modules/Wims/Services/`

Rute utama yang terlihat di repo:
- `/wims` untuk entry point modul
- `/wims/dashboard` untuk mahasiswa
- `/wims/admin/*` untuk admin
- `/wims/dosen/*` untuk dosen
- `/wims/mitra/*` untuk mitra

## 8. Data Dan Entitas Produk

Berdasarkan struktur kode dan migrasi, WIMS bekerja dengan konsep data berikut:
- pendaftaran magang
- penempatan mahasiswa
- perusahaan mitra
- presensi / absensi
- logbook
- ketidakhadiran
- penilaian mahasiswa
- template penilaian
- template laporan akhir
- laporan akhir

## 9. Integrasi Portal

WIMS berjalan sebagai bagian dari portal utama, bukan aplikasi berdiri sendiri.

Karakteristik integrasi:
- autentikasi mengikuti portal utama
- konteks modul diatur lewat middleware `module.context:wims`
- akses dibatasi berdasarkan role aktif
- halaman WIMS dirender melalui Inertia Vue

## 10. Catatan Teknis Yang Relevan Untuk Scrum Master

- WIMS di repo ini adalah modul lokal yang masih berada dalam PR belum di-merge
- dokumentasi produk ini disusun dari implementasi lokal, bukan dari dokumen formal yang sudah dipublikasikan
- jika scrum master membutuhkan versi final, perlu sinkronisasi ulang setelah PR masuk ke branch utama

## 11. Ringkasan Singkat Untuk Dikirim

Kalau ingin jawaban singkat ke scrum master, pakai versi ini:

> Ya, WIMS sudah ada implementasinya di repo lokal, tetapi masih berada di PR yang belum di-merge. Dari sisi produk, WIMS adalah Web-based Internship Management System untuk pendaftaran, penempatan, presensi, logbook, monitoring, penilaian, dan laporan akhir magang FMIKOM. Dokumen produk sementara sudah saya susun berdasarkan state lokal saat ini.

## 12. Referensi Kode Lokal

- `routes/wims.php`
- `database/seeders/ModuleSeeder.php`
- `app/Modules/Wims/Controllers/WimsDashboardController.php`
- `app/Modules/Wims/`
- `resources/js/pages/Modules/Wims/`

