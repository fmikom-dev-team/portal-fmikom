# Use Case Diagram WIMS

Dokumen ini merangkum use case diagram untuk modul **WIMS (Web-based Internship Management System)** berdasarkan implementasi lokal pada repo saat ini.

## Aktor Utama

- Mahasiswa
- Dosen
- Mitra
- Admin

Catatan:
- Role `Admin` pada implementasi portal mencakup `super-admin`, `admin`, `admin-universitas`, `admin-akademik`, dan `prodi`.
- Diagram berikut disusun dari fitur yang tersedia pada `routes/wims.php` dan struktur modul `app/Modules/Wims/`.

## 1. Use Case Diagram Keseluruhan

```mermaid
flowchart LR
    mahasiswa[Mahasiswa]
    dosen[Dosen]
    mitra[Mitra]
    admin[Admin]

    subgraph WIMS["Sistem WIMS"]
        uc1([Kelola profil mahasiswa])
        uc2([Daftar magang])
        uc3([Unduh template proposal])
        uc4([Lakukan absensi masuk])
        uc5([Lakukan checkout absensi])
        uc6([Lihat dan unduh riwayat absensi])
        uc7([Kelola logbook harian])
        uc8([Unduh logbook])
        uc9([Ajukan ketidakhadiran])
        uc10([Unggah laporan akhir])
        uc11([Lihat dan unduh laporan akhir])
        uc12([Lihat dashboard per role])
        uc13([Monitoring mahasiswa])
        uc14([Lihat detail monitoring])
        uc15([Beri penilaian mahasiswa])
        uc16([Review logbook mahasiswa])
        uc17([Setujui atau tolak ketidakhadiran])
        uc18([Kelola data perusahaan mitra])
        uc19([Aktivasi akun mitra])
        uc20([Verifikasi pendaftaran magang])
        uc21([Unduh proposal mahasiswa])
        uc22([Kelola penempatan mahasiswa])
        uc23([Aktifkan atau selesaikan penempatan])
        uc24([Monitoring keseluruhan])
        uc25([Unduh data absensi dan logbook])
        uc26([Kelola template laporan akhir])
        uc27([Kelola template penilaian])
        uc28([Lihat dan unduh rekap nilai])
    end

    mahasiswa --> uc1
    mahasiswa --> uc2
    mahasiswa --> uc3
    mahasiswa --> uc4
    mahasiswa --> uc5
    mahasiswa --> uc6
    mahasiswa --> uc7
    mahasiswa --> uc8
    mahasiswa --> uc9
    mahasiswa --> uc10
    mahasiswa --> uc11
    mahasiswa --> uc12

    dosen --> uc12
    dosen --> uc13
    dosen --> uc14
    dosen --> uc15
    dosen --> uc11

    mitra --> uc12
    mitra --> uc13
    mitra --> uc14
    mitra --> uc15
    mitra --> uc16
    mitra --> uc17
    mitra --> uc11

    admin --> uc12
    admin --> uc18
    admin --> uc19
    admin --> uc20
    admin --> uc21
    admin --> uc22
    admin --> uc23
    admin --> uc24
    admin --> uc25
    admin --> uc26
    admin --> uc27
    admin --> uc28
end
```

## 2. Use Case Diagram Mahasiswa

```mermaid
flowchart LR
    mahasiswa[Mahasiswa]

    subgraph WIMS_Mahasiswa["Modul WIMS - Mahasiswa"]
        m1([Lihat dashboard mahasiswa])
        m2([Kelola profil])
        m3([Daftar magang])
        m4([Unduh template proposal])
        m5([Absensi masuk])
        m6([Checkout absensi])
        m7([Lihat riwayat absensi])
        m8([Unduh riwayat absensi])
        m9([Tambah logbook])
        m10([Ubah logbook])
        m11([Unduh logbook])
        m12([Ajukan ketidakhadiran])
        m13([Batalkan pengajuan ketidakhadiran])
        m14([Unggah laporan akhir])
        m15([Lihat laporan akhir])
        m16([Unduh template laporan akhir])
        m17([Unduh laporan akhir])
    end

    mahasiswa --> m1
    mahasiswa --> m2
    mahasiswa --> m3
    mahasiswa --> m4
    mahasiswa --> m5
    mahasiswa --> m6
    mahasiswa --> m7
    mahasiswa --> m8
    mahasiswa --> m9
    mahasiswa --> m10
    mahasiswa --> m11
    mahasiswa --> m12
    mahasiswa --> m13
    mahasiswa --> m14
    mahasiswa --> m15
    mahasiswa --> m16
    mahasiswa --> m17
```

## 3. Use Case Diagram Dosen

```mermaid
flowchart LR
    dosen[Dosen]

    subgraph WIMS_Dosen["Modul WIMS - Dosen"]
        d1([Lihat dashboard dosen])
        d2([Lihat daftar monitoring mahasiswa])
        d3([Lihat detail monitoring mahasiswa])
        d4([Lihat daftar penilaian mahasiswa])
        d5([Lihat detail penilaian mahasiswa])
        d6([Beri penilaian mahasiswa])
        d7([Lihat laporan akhir mahasiswa])
        d8([Unduh laporan akhir mahasiswa])
    end

    dosen --> d1
    dosen --> d2
    dosen --> d3
    dosen --> d4
    dosen --> d5
    dosen --> d6
    dosen --> d7
    dosen --> d8
```

## 4. Use Case Diagram Mitra

```mermaid
flowchart LR
    mitra[Mitra]

    subgraph WIMS_Mitra["Modul WIMS - Mitra"]
        t1([Lihat dashboard mitra])
        t2([Lihat daftar monitoring mahasiswa])
        t3([Lihat detail monitoring mahasiswa])
        t4([Lihat daftar penilaian mahasiswa])
        t5([Lihat detail penilaian mahasiswa])
        t6([Beri penilaian mahasiswa])
        t7([Lihat laporan akhir mahasiswa])
        t8([Unduh laporan akhir mahasiswa])
        t9([Review logbook mahasiswa])
        t10([Setujui ketidakhadiran])
        t11([Tolak ketidakhadiran])
    end

    mitra --> t1
    mitra --> t2
    mitra --> t3
    mitra --> t4
    mitra --> t5
    mitra --> t6
    mitra --> t7
    mitra --> t8
    mitra --> t9
    mitra --> t10
    mitra --> t11
```

## 5. Use Case Diagram Admin

```mermaid
flowchart LR
    admin[Admin]

    subgraph WIMS_Admin["Modul WIMS - Admin"]
        a1([Lihat dashboard admin])
        a2([Lihat data perusahaan mitra])
        a3([Tambah perusahaan mitra])
        a4([Ubah perusahaan mitra])
        a5([Hapus perusahaan mitra])
        a6([Aktivasi atau buat akun mitra])
        a7([Lihat pendaftaran magang])
        a8([Verifikasi status pendaftaran])
        a9([Bulk approve pendaftaran])
        a10([Unduh proposal mahasiswa])
        a11([Lihat data penempatan])
        a12([Ubah penempatan mahasiswa])
        a13([Aktifkan penempatan])
        a14([Selesaikan penempatan])
        a15([Selesaikan penempatan secara massal])
        a16([Lihat monitoring keseluruhan])
        a17([Lihat detail monitoring mahasiswa])
        a18([Unduh data absensi])
        a19([Unduh data logbook])
        a20([Lihat rekap nilai])
        a21([Unduh rekap nilai dosen])
        a22([Unduh rekap nilai mitra])
        a23([Kelola template proposal atau laporan akhir])
        a24([Kelola template penilaian])
    end

    admin --> a1
    admin --> a2
    admin --> a3
    admin --> a4
    admin --> a5
    admin --> a6
    admin --> a7
    admin --> a8
    admin --> a9
    admin --> a10
    admin --> a11
    admin --> a12
    admin --> a13
    admin --> a14
    admin --> a15
    admin --> a16
    admin --> a17
    admin --> a18
    admin --> a19
    admin --> a20
    admin --> a21
    admin --> a22
    admin --> a23
    admin --> a24
```

## 6. Narasi Singkat Untuk Skripsi

Narasi yang bisa dipakai:

> Use case diagram modul WIMS menggambarkan interaksi empat aktor utama, yaitu mahasiswa, dosen, mitra, dan admin, dengan sistem pengelolaan magang berbasis web. Mahasiswa berfokus pada proses operasional magang seperti pendaftaran, absensi, logbook, ketidakhadiran, dan laporan akhir. Dosen dan mitra berperan dalam monitoring serta penilaian mahasiswa, sedangkan admin mengelola aspek administratif seperti perusahaan mitra, verifikasi pendaftaran, penempatan, monitoring keseluruhan, template dokumen, dan rekap nilai.

## 7. Referensi Implementasi

- `routes/wims.php`
- `app/Modules/Wims/Controllers/`
- `app/Modules/Wims/Services/`
- `docs/wims-product-documentation.md`

