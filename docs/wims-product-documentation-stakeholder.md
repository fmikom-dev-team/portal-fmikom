# WIMS Product Documentation

## Executive Summary

WIMS adalah **Web-based Internship Management System** untuk mendukung pengelolaan PKL dan magang FMIKOM secara terpusat. Produk ini dirancang untuk mengakomodasi proses pendaftaran, penempatan, presensi, logbook, monitoring, penilaian, dan laporan akhir dalam satu alur kerja yang terintegrasi.

Dokumen ini disusun berdasarkan implementasi WIMS yang tersedia di repository lokal saat ini. Perlu dicatat bahwa terdapat PR WIMS yang belum di-merge, sehingga isi dokumen ini merepresentasikan status produk lokal dan bukan rilis final.

## Product Objective

WIMS bertujuan untuk:
- menyederhanakan proses administrasi magang
- menyediakan satu sumber data untuk seluruh aktivitas magang
- meningkatkan visibilitas proses bagi mahasiswa, dosen, mitra, dan admin
- memudahkan evaluasi dan rekap hasil melalui alur yang terdokumentasi

## Product Scope

WIMS mencakup empat kelompok pengguna utama:
- mahasiswa
- dosen
- mitra
- admin

Admin pada implementasi ini meliputi beberapa sub-role portal:
- super-admin
- admin
- admin-universitas
- admin-akademik
- prodi

## Core Capabilities

### Mahasiswa
- mengelola profil
- mendaftar program magang
- melakukan absensi
- mengisi logbook
- mengajukan ketidakhadiran
- mengunggah laporan akhir
- mengunduh template proposal dan laporan

### Dosen
- memantau mahasiswa bimbingan
- meninjau perkembangan monitoring
- memberikan penilaian mahasiswa
- melihat laporan akhir mahasiswa

### Mitra
- memantau mahasiswa yang ditempatkan
- meninjau logbook
- menyetujui atau menolak ketidakhadiran
- memberikan penilaian mahasiswa
- melihat laporan akhir mahasiswa

### Admin
- mengelola data perusahaan mitra
- memverifikasi pendaftaran mahasiswa
- mengatur penempatan mahasiswa
- memantau pelaksanaan magang
- menyiapkan template penilaian
- menyiapkan template laporan akhir
- mengunduh rekap penilaian

## End-to-End Process

Alur produk WIMS secara umum adalah sebagai berikut:

1. Mahasiswa masuk ke portal dan membuka modul WIMS.
2. Mahasiswa melengkapi profil dan mengajukan pendaftaran magang.
3. Admin memverifikasi pendaftaran dan mengelola penempatan.
4. Mahasiswa menjalankan absensi, logbook, dan pengajuan ketidakhadiran selama periode magang.
5. Dosen dan mitra memantau aktivitas serta memberikan penilaian.
6. Mahasiswa mengunggah laporan akhir dan mengakses dokumen pendukung yang tersedia.
7. Admin melakukan rekap hasil dan pengelolaan template administratif.

## Business Value

WIMS memberikan nilai bisnis berikut:
- mengurangi proses manual dalam pengelolaan magang
- menyatukan data operasional dan akademik dalam satu modul
- mempercepat koordinasi antara pihak internal dan mitra
- menyediakan jejak proses yang lebih jelas untuk monitoring dan evaluasi

## Implementation Status

Berdasarkan repo lokal saat ini, WIMS sudah memiliki komponen berikut:
- routing modul
- controller per peran
- service layer untuk proses bisnis
- halaman Inertia Vue untuk masing-masing peran
- struktur data dan migrasi pendukung

Status implementasi:
- modul WIMS sudah tersedia di repo lokal
- ada PR yang belum di-merge
- dokumen ini harus dianggap sebagai snapshot operasional saat ini

## Technical Integration

WIMS diintegrasikan ke portal utama, bukan aplikasi terpisah. Karakteristik integrasinya:
- autentikasi mengikuti portal utama
- akses modul dibatasi berdasarkan role
- konteks modul diatur melalui middleware `module.context:wims`
- antarmuka dirender menggunakan Inertia Vue

## Key Risks and Notes

- Karena masih ada PR yang belum di-merge, struktur fitur dapat berubah sebelum rilis final.
- Dokumen ini perlu disinkronkan ulang setelah PR masuk ke branch utama.
- Untuk komunikasi stakeholder, status WIMS sebaiknya disebut sebagai "sudah tersedia di repo lokal, tetapi belum final di branch utama".

## Reference Files

- `routes/wims.php`
- `database/seeders/ModuleSeeder.php`
- `app/Modules/Wims/`
- `resources/js/pages/Modules/Wims/`

## Stakeholder Statement

Versi singkat yang dapat digunakan untuk laporan ke stakeholder:

> WIMS sudah tersedia di repository lokal sebagai modul pengelolaan PKL dan magang FMIKOM yang mencakup pendaftaran, penempatan, presensi, logbook, monitoring, penilaian, dan laporan akhir. Namun, implementasi tersebut masih berada pada PR yang belum di-merge, sehingga dokumen ini merepresentasikan status produk lokal saat ini dan belum dianggap sebagai rilis final.

