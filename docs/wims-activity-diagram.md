# Activity Diagram WIMS

Dokumen ini berisi activity diagram modul **WIMS (Web-based Internship Management System)** berdasarkan implementasi lokal pada repo saat ini.

## Aktor Utama

- Mahasiswa
- Dosen
- Mitra
- Admin

Catatan:
- Role `Admin` pada implementasi portal mencakup `super-admin`, `admin`, `admin-universitas`, `admin-akademik`, dan `prodi`.
- Diagram berikut disusun dari alur yang tersedia pada `routes/wims.php` dan struktur modul `app/Modules/Wims/`.

## 1. Activity Diagram Keseluruhan

```mermaid
flowchart TD
    A([Mulai]) --> B[Login ke portal]
    B --> C[Masuk ke modul WIMS]
    C --> D{Role pengguna}

    D -->|Mahasiswa| E[Lengkapi atau ubah profil]
    E --> F[Ajukan pendaftaran magang]
    F --> G[Admin memverifikasi pendaftaran]
    G --> H{Pendaftaran disetujui?}

    H -->|Tidak| I[Mahasiswa memperbaiki atau mengajukan ulang pendaftaran]
    I --> F

    H -->|Ya| J[Admin mengatur penempatan mahasiswa]
    J --> K[Admin mengaktifkan penempatan]
    K --> L[Mahasiswa menjalani kegiatan magang]

    L --> M[Mahasiswa absensi masuk]
    M --> N[Mahasiswa mengisi logbook]
    N --> O{Perlu ketidakhadiran?}

    O -->|Ya| P[Mahasiswa mengajukan ketidakhadiran]
    P --> Q[Mitra menyetujui atau menolak ketidakhadiran]
    Q --> R[Mahasiswa checkout absensi]

    O -->|Tidak| R

    R --> S[Mitra melakukan review logbook]
    S --> T[Dosen dan mitra melakukan monitoring]
    T --> U[Dosen memberi penilaian mahasiswa]
    U --> V[Mitra memberi penilaian mahasiswa]
    V --> W[Mahasiswa mengunggah laporan akhir]
    W --> X[Admin melihat monitoring dan rekap nilai]
    X --> Y([Selesai])
```

## 2. Activity Diagram Mahasiswa

```mermaid
flowchart TD
    A([Mulai]) --> B[Login]
    B --> C[Masuk dashboard mahasiswa]
    C --> D[Lihat atau ubah profil]
    D --> E[Unduh template proposal]
    E --> F[Ajukan pendaftaran magang]
    F --> G{Pendaftaran disetujui admin?}

    G -->|Tidak| H[Perbaiki data pendaftaran]
    H --> F

    G -->|Ya| I[Mulai periode magang]
    I --> J[Absensi masuk]
    J --> K[Tambah logbook]
    K --> L{Perlu izin tidak hadir?}

    L -->|Ya| M[Ajukan ketidakhadiran]
    M --> N[Menunggu keputusan mitra]
    N --> O[Checkout absensi]

    L -->|Tidak| O

    O --> P{Periode magang selesai?}
    P -->|Belum| J
    P -->|Ya| Q[Unggah laporan akhir]
    Q --> R[Lihat laporan akhir]
    R --> S[Unduh template atau laporan akhir]
    S --> T[Unduh riwayat absensi atau logbook]
    T --> U([Selesai])
```

## 3. Activity Diagram Dosen

```mermaid
flowchart TD
    A([Mulai]) --> B[Login]
    B --> C[Masuk dashboard dosen]
    C --> D[Lihat daftar mahasiswa bimbingan]
    D --> E[Buka monitoring mahasiswa]
    E --> F[Lihat detail monitoring]
    F --> G[Buka penilaian mahasiswa]
    G --> H[Lihat laporan akhir mahasiswa]
    H --> I[Isi penilaian mahasiswa]
    I --> J[Simpan penilaian]
    J --> K([Selesai])
```

## 4. Activity Diagram Mitra

```mermaid
flowchart TD
    A([Mulai]) --> B[Login]
    B --> C[Masuk dashboard mitra]
    C --> D[Lihat daftar mahasiswa magang]
    D --> E[Buka monitoring mahasiswa]
    E --> F[Lihat detail monitoring]
    F --> G[Review logbook mahasiswa]
    G --> H{Ada pengajuan ketidakhadiran?}

    H -->|Ya| I[Setujui atau tolak ketidakhadiran]
    I --> J[Buka penilaian mahasiswa]

    H -->|Tidak| J

    J --> K[Lihat laporan akhir mahasiswa]
    K --> L[Isi penilaian mahasiswa]
    L --> M[Simpan penilaian]
    M --> N([Selesai])
```

## 5. Activity Diagram Admin

```mermaid
flowchart TD
    A([Mulai]) --> B[Login]
    B --> C[Masuk dashboard admin]
    C --> D[Kelola data perusahaan mitra]
    D --> E[Aktivasi atau buat akun mitra]
    E --> F[Lihat daftar pendaftaran magang]
    F --> G[Periksa kelengkapan pendaftaran]
    G --> H{Pendaftaran valid?}

    H -->|Tidak| I[Tolak atau minta perbaikan]
    I --> F

    H -->|Ya| J[Setujui pendaftaran]
    J --> K[Kelola penempatan mahasiswa]
    K --> L[Aktifkan penempatan]
    L --> M{Magang selesai?}

    M -->|Belum| N[Lihat monitoring keseluruhan]
    N --> O[Lihat detail monitoring]
    O --> P[Unduh data absensi atau logbook]
    P --> N

    M -->|Ya| Q[Selesaikan penempatan]
    Q --> R[Lihat rekap nilai]
    R --> S[Unduh rekap nilai dosen atau mitra]
    S --> T[Kelola template laporan akhir]
    T --> U[Kelola template penilaian]
    U --> V([Selesai])
```

## 6. Activity Diagram Proses Pendaftaran Dan Penempatan

```mermaid
flowchart TD
    A([Mulai]) --> B[Mahasiswa login]
    B --> C[Mahasiswa melengkapi profil]
    C --> D[Mahasiswa mengunduh template proposal]
    D --> E[Mahasiswa mengajukan pendaftaran]
    E --> F[Admin meninjau pendaftaran]
    F --> G{Pendaftaran valid?}

    G -->|Tidak| H[Admin menolak atau meminta revisi]
    H --> I[Mahasiswa memperbaiki data]
    I --> E

    G -->|Ya| J[Admin menyetujui pendaftaran]
    J --> K[Admin menetapkan mitra]
    K --> L[Admin mengaktifkan penempatan]
    L --> M([Selesai])
```

## 7. Activity Diagram Proses Operasional Harian Magang

```mermaid
flowchart TD
    A([Mulai]) --> B[Mahasiswa login]
    B --> C[Mahasiswa absensi masuk]
    C --> D[Mahasiswa mengisi logbook]
    D --> E{Perlu ketidakhadiran?}

    E -->|Ya| F[Mahasiswa mengajukan ketidakhadiran]
    F --> G[Mitra memutuskan pengajuan]
    G --> H[Mahasiswa checkout absensi]

    E -->|Tidak| H

    H --> I[Mitra review logbook]
    I --> J[Dosen memantau perkembangan]
    J --> K[Mitra memantau perkembangan]
    K --> L{Periode masih berjalan?}
    L -->|Ya| C
    L -->|Tidak| M([Selesai])
```

## 8. Activity Diagram Proses Penilaian Dan Laporan Akhir

```mermaid
flowchart TD
    A([Mulai]) --> B[Mahasiswa mengunggah laporan akhir]
    B --> C[Dosen melihat laporan akhir]
    C --> D[Dosen mengisi penilaian]
    D --> E[Mitra melihat laporan akhir]
    E --> F[Mitra mengisi penilaian]
    F --> G[Admin melihat rekap nilai]
    G --> H[Admin mengunduh rekap nilai]
    H --> I([Selesai])
```

## 9. Narasi Singkat Untuk Skripsi

Narasi yang dapat digunakan:

> Activity diagram modul WIMS menggambarkan alur aktivitas utama pada sistem pengelolaan magang berbasis web. Proses dimulai dari mahasiswa yang melakukan pendaftaran magang, dilanjutkan dengan verifikasi oleh admin, penempatan ke mitra, pelaksanaan kegiatan harian seperti absensi dan logbook, monitoring oleh dosen dan mitra, penilaian, hingga pengunggahan laporan akhir dan rekapitulasi hasil. Diagram ini menunjukkan bahwa setiap aktor memiliki peran yang berbeda, tetapi saling terhubung dalam satu proses bisnis magang yang terintegrasi.

## 10. Referensi Implementasi

- `routes/wims.php`
- `app/Modules/Wims/Controllers/`
- `app/Modules/Wims/Services/`
- `docs/wims-product-documentation.md`
- `docs/wims-user-stories-backlog.md`
