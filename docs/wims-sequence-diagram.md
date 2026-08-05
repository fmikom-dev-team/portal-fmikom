# Sequence Diagram WIMS

Dokumen ini berisi **sequence diagram** modul **WIMS (Web-based Internship Management System)** berdasarkan alur aktif pada repo saat ini.

## Dasar Penyusunan

Dokumen ini disusun dari:

- `routes/wims.php`
- `app/Modules/Wims/Controllers/`
- `app/Modules/Wims/Services/`
- `app/Models/Magang/`

Catatan:

- Diagram difokuskan pada **interaksi antar aktor dan sistem WIMS**.
- Diagram memakai aktor utama:
  - `Mahasiswa`
  - `Admin`
  - `Dosen`
  - `Mitra`
  - `WIMS`
- Alur penilaian memakai skema aktif `assessment_templates`, `assessment_submissions`, dan `assessment_scores`, bukan tabel penilaian legacy.

## 1. Sequence Diagram Keseluruhan

```mermaid
sequenceDiagram
    autonumber
    actor Mahasiswa
    participant WIMS
    actor Admin
    actor Mitra
    actor Dosen

    Mahasiswa->>WIMS: Login dan masuk modul WIMS
    Mahasiswa->>WIMS: Lengkapi profil
    Mahasiswa->>WIMS: Ajukan pendaftaran magang
    WIMS-->>Admin: Data pendaftaran baru tersedia

    Admin->>WIMS: Verifikasi pendaftaran
    alt Pendaftaran perlu revisi atau ditolak
        WIMS-->>Mahasiswa: Status revisi / ditolak
        Mahasiswa->>WIMS: Perbaiki dan ajukan ulang
        WIMS-->>Admin: Data pendaftaran diperbarui
        Admin->>WIMS: Verifikasi ulang
    else Pendaftaran disetujui
        Admin->>WIMS: Atur penempatan mahasiswa
        Admin->>WIMS: Aktifkan penempatan
        WIMS-->>Mahasiswa: Status aktif dan penempatan tersedia
        WIMS-->>Mitra: Data mahasiswa magang tersedia
        WIMS-->>Dosen: Data mahasiswa bimbingan tersedia
    end

    loop Selama periode magang
        Mahasiswa->>WIMS: Absensi masuk
        Mahasiswa->>WIMS: Isi logbook
        opt Jika perlu izin tidak hadir
            Mahasiswa->>WIMS: Ajukan ketidakhadiran
            Mitra->>WIMS: Setujui / tolak ketidakhadiran
            WIMS-->>Mahasiswa: Hasil review ketidakhadiran
        end
        Mahasiswa->>WIMS: Checkout absensi
        Mitra->>WIMS: Review logbook
        Dosen->>WIMS: Monitoring mahasiswa
        Mitra->>WIMS: Monitoring mahasiswa
    end

    Mahasiswa->>WIMS: Unggah laporan akhir
    Dosen->>WIMS: Isi penilaian mahasiswa
    Mitra->>WIMS: Isi penilaian mahasiswa
    Admin->>WIMS: Lihat monitoring dan rekap nilai
    Admin->>WIMS: Unduh rekap nilai
```

## 2. Sequence Diagram Mahasiswa

```mermaid
sequenceDiagram
    autonumber
    actor Mahasiswa
    participant WIMS
    actor Admin
    actor Mitra

    Mahasiswa->>WIMS: Login
    Mahasiswa->>WIMS: Buka dashboard mahasiswa
    Mahasiswa->>WIMS: Lihat / ubah profil
    Mahasiswa->>WIMS: Unduh template proposal
    Mahasiswa->>WIMS: Ajukan pendaftaran magang
    WIMS-->>Admin: Data pendaftaran masuk

    Admin->>WIMS: Verifikasi pendaftaran
    alt Pendaftaran belum valid
        WIMS-->>Mahasiswa: Status revisi / ditolak
        Mahasiswa->>WIMS: Perbaiki pendaftaran
        Mahasiswa->>WIMS: Ajukan ulang
    else Pendaftaran disetujui
        WIMS-->>Mahasiswa: Status pendaftaran disetujui
    end

    loop Saat magang aktif
        Mahasiswa->>WIMS: Absensi masuk
        Mahasiswa->>WIMS: Tambah / ubah logbook
        opt Perlu ketidakhadiran
            Mahasiswa->>WIMS: Ajukan ketidakhadiran
            Mitra->>WIMS: Review ketidakhadiran
            WIMS-->>Mahasiswa: Hasil review ketidakhadiran
        end
        Mahasiswa->>WIMS: Checkout absensi
    end

    Mahasiswa->>WIMS: Unggah laporan akhir
    Mahasiswa->>WIMS: Lihat laporan akhir
    Mahasiswa->>WIMS: Unduh laporan akhir / template
```

## 3. Sequence Diagram Admin

```mermaid
sequenceDiagram
    autonumber
    actor Admin
    participant WIMS
    actor Mahasiswa
    actor Mitra

    Admin->>WIMS: Login
    Admin->>WIMS: Buka dashboard admin
    Admin->>WIMS: Kelola data perusahaan mitra

    opt Aktivasi akun mitra
        Admin->>WIMS: Buat / aktifkan akun mitra
        WIMS-->>Mitra: Akun mitra aktif
    end

    Admin->>WIMS: Buka daftar pendaftaran
    WIMS-->>Admin: Tampilkan data pendaftaran
    Admin->>WIMS: Verifikasi status pendaftaran
    WIMS-->>Mahasiswa: Status pendaftaran diperbarui

    opt Proposal perlu dicek
        Admin->>WIMS: Unduh proposal mahasiswa
    end

    Admin->>WIMS: Kelola penempatan mahasiswa
    Admin->>WIMS: Aktifkan / selesaikan penempatan
    WIMS-->>Mahasiswa: Informasi penempatan diperbarui
    WIMS-->>Mitra: Data mahasiswa penempatan tersedia

    Admin->>WIMS: Buka monitoring keseluruhan
    Admin->>WIMS: Lihat detail monitoring
    Admin->>WIMS: Unduh absensi / logbook
    Admin->>WIMS: Kelola template laporan akhir
    Admin->>WIMS: Kelola template penilaian
    Admin->>WIMS: Lihat dan unduh rekap nilai
```

## 4. Sequence Diagram Dosen

```mermaid
sequenceDiagram
    autonumber
    actor Dosen
    participant WIMS
    actor Mahasiswa

    Dosen->>WIMS: Login
    Dosen->>WIMS: Buka dashboard dosen
    WIMS-->>Dosen: Tampilkan mahasiswa bimbingan
    Dosen->>WIMS: Buka monitoring mahasiswa
    WIMS-->>Dosen: Tampilkan detail monitoring
    Dosen->>WIMS: Buka halaman penilaian
    WIMS-->>Dosen: Tampilkan form penilaian dan laporan akhir
    Dosen->>WIMS: Lihat / unduh laporan akhir mahasiswa
    Dosen->>WIMS: Input nilai per komponen
    Dosen->>WIMS: Simpan / submit penilaian
    WIMS-->>Mahasiswa: Status penilaian dosen tersimpan
```

## 5. Sequence Diagram Mitra

```mermaid
sequenceDiagram
    autonumber
    actor Mitra
    participant WIMS
    actor Mahasiswa

    Mitra->>WIMS: Login
    Mitra->>WIMS: Buka dashboard mitra
    WIMS-->>Mitra: Tampilkan mahasiswa magang
    Mitra->>WIMS: Buka monitoring mahasiswa
    WIMS-->>Mitra: Tampilkan detail monitoring
    Mitra->>WIMS: Review logbook mahasiswa
    WIMS-->>Mahasiswa: Status review logbook diperbarui

    opt Ada pengajuan ketidakhadiran
        Mitra->>WIMS: Setujui / tolak ketidakhadiran
        WIMS-->>Mahasiswa: Hasil review ketidakhadiran
    end

    Mitra->>WIMS: Buka halaman penilaian
    WIMS-->>Mitra: Tampilkan form penilaian dan laporan akhir
    Mitra->>WIMS: Lihat / unduh laporan akhir mahasiswa
    Mitra->>WIMS: Input nilai per komponen
    Mitra->>WIMS: Simpan / submit penilaian
    WIMS-->>Mahasiswa: Status penilaian mitra tersimpan
```

## 6. Sequence Diagram Pendaftaran Dan Penempatan

```mermaid
sequenceDiagram
    autonumber
    actor Mahasiswa
    participant WIMS
    actor Admin
    actor Mitra

    Mahasiswa->>WIMS: Lengkapi profil
    Mahasiswa->>WIMS: Unduh template proposal
    Mahasiswa->>WIMS: Ajukan pendaftaran
    WIMS-->>Admin: Data pendaftaran masuk

    Admin->>WIMS: Tinjau pendaftaran
    alt Data tidak valid
        Admin->>WIMS: Set status revisi / ditolak
        WIMS-->>Mahasiswa: Hasil verifikasi
        Mahasiswa->>WIMS: Perbaiki dan ajukan ulang
    else Data valid
        Admin->>WIMS: Set status approved
        Admin->>WIMS: Tetapkan perusahaan / dosen / pembimbing
        Admin->>WIMS: Aktifkan penempatan
        WIMS-->>Mahasiswa: Penempatan aktif
        WIMS-->>Mitra: Mahasiswa baru tersedia
    end
```

## 7. Sequence Diagram Operasional Harian Magang

```mermaid
sequenceDiagram
    autonumber
    actor Mahasiswa
    participant WIMS
    actor Mitra
    actor Dosen

    loop Aktivitas harian
        Mahasiswa->>WIMS: Absensi masuk
        Mahasiswa->>WIMS: Isi logbook
        opt Perlu izin tidak hadir
            Mahasiswa->>WIMS: Ajukan ketidakhadiran
            Mitra->>WIMS: Review ketidakhadiran
            WIMS-->>Mahasiswa: Status ketidakhadiran
        end
        Mahasiswa->>WIMS: Checkout absensi
        Mitra->>WIMS: Review logbook
        Dosen->>WIMS: Lihat monitoring
        Mitra->>WIMS: Lihat monitoring
    end
```

## 8. Sequence Diagram Penilaian Dan Laporan Akhir

```mermaid
sequenceDiagram
    autonumber
    actor Mahasiswa
    participant WIMS
    actor Dosen
    actor Mitra
    actor Admin

    Mahasiswa->>WIMS: Unggah laporan akhir
    Dosen->>WIMS: Buka penilaian mahasiswa
    WIMS-->>Dosen: Template dan laporan akhir tersedia
    Dosen->>WIMS: Simpan / submit penilaian

    Mitra->>WIMS: Buka penilaian mahasiswa
    WIMS-->>Mitra: Template dan laporan akhir tersedia
    Mitra->>WIMS: Simpan / submit penilaian

    Admin->>WIMS: Buka rekap nilai
    WIMS-->>Admin: Tampilkan nilai dosen dan mitra
    Admin->>WIMS: Unduh rekap nilai
```

## 9. Penjelasan Singkat Sequence Diagram

Sequence diagram WIMS menjelaskan **urutan komunikasi** antara aktor dan sistem.

Makna tiap diagram:

- **Diagram keseluruhan** menunjukkan alur end-to-end dari pendaftaran hingga penilaian akhir.
- **Diagram mahasiswa** menekankan interaksi mahasiswa sejak pendaftaran, kegiatan harian, hingga laporan akhir.
- **Diagram admin** menekankan fungsi administratif seperti verifikasi, penempatan, monitoring, dan pengelolaan template.
- **Diagram dosen** menunjukkan monitoring dan penilaian akademik mahasiswa.
- **Diagram mitra** menunjukkan monitoring lapangan, review logbook, review ketidakhadiran, dan penilaian lapangan.
- **Diagram pendaftaran dan penempatan** memfokuskan alur awal sebelum magang aktif.
- **Diagram operasional harian** memfokuskan absensi, logbook, ketidakhadiran, dan monitoring.
- **Diagram penilaian dan laporan akhir** memfokuskan unggah laporan, penilaian dosen/mitra, dan rekap nilai admin.

## 10. Narasi Singkat Untuk Skripsi

Narasi yang dapat digunakan:

> Sequence diagram modul WIMS menggambarkan urutan interaksi antara mahasiswa, admin, dosen, mitra, dan sistem dalam setiap proses utama. Diagram ini menunjukkan bahwa mahasiswa memulai proses melalui pendaftaran, kemudian admin melakukan verifikasi dan penempatan, setelah itu mahasiswa menjalankan aktivitas harian magang yang dimonitor oleh dosen dan mitra. Pada tahap akhir, mahasiswa mengunggah laporan akhir, dosen dan mitra memberikan penilaian, dan admin melakukan rekapitulasi hasil. Dengan demikian, sequence diagram memperjelas aliran pesan dan tanggung jawab setiap aktor dalam modul WIMS.

## 11. Referensi Implementasi

- `routes/wims.php`
- `app/Modules/Wims/Controllers/`
- `app/Modules/Wims/Services/`
- `app/Models/Magang/`
