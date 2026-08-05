# ERD dan Class Diagram WIMS

Dokumen ini merangkum **ERD** dan **class diagram** modul **WIMS (Web-based Internship Management System)** berdasarkan **migrasi dan model aktif** di repo saat ini.

## Dasar Validasi

Dokumen ini disusun dari:

- `database/migrations/magang/`
- `database/migrations/core/`
- `database/migrations/2026_07_01_000000_create_final_report_templates_table.php`
- `database/migrations/2026_07_04_000000_add_template_type_to_final_report_templates_table.php`
- `app/Models/Magang/`
- `app/Modules/Wims/Services/`
- `routes/wims.php`

Catatan validasi:

- Saya **tidak menggunakan tabel legacy/fallback** bila fitur aktif WIMS sudah memakai tabel baru.
- Saya **tidak memasukkan kolom `users.nim_nip`** karena kolom itu sudah dihapus oleh migrasi aktif; field identitas aktif yang dipakai database adalah `users.nomor_induk`.
- Saya **tidak menjadikan `penilaian_magangs` sebagai tabel utama ERD WIMS**, karena implementasi WIMS aktif memakai `assessment_templates`, `assessment_components`, `assessment_submissions`, dan `assessment_scores`.
- Saya **tidak menarik tabel `surats` ke diagram inti**, walaupun `pendaftaran_magangs` masih memiliki `surat_tugas_id`, karena relasi itu belum menjadi alur aktif utama di service/controller WIMS saat ini.
- Tabel `final_report_templates` memakai skema aktif yang mencakup kolom `template_type`. Ada file migrasi `.disabled` di folder `magang`, tetapi itu bukan sumber skema aktif.

## 1. Tabel Aktif Yang Dipakai WIMS

Tabel inti yang aktif dipakai modul:

- `users`
- `program_studis`
- `perusahaan_mitras`
- `pembimbing_lapangans`
- `pendaftaran_magangs`
- `absensi_magangs`
- `logbook_magangs`
- `logbook_photos`
- `ketidakhadiran_magangs`
- `hari_liburs`
- `assessment_templates`
- `assessment_components`
- `assessment_submissions`
- `assessment_scores`
- `final_report_templates`
- `surat_penetapans`

## 2. ERD WIMS

```mermaid
erDiagram
    USERS {
        bigint id PK
        bigint program_studi_id FK
        string name
        string email
        string user_type
        string nomor_induk
        string no_telepon
        string foto_path
        boolean is_active
        date tanggal_lahir
        text bio
        string linkedin
    }

    PROGRAM_STUDIS {
        bigint id PK
        bigint fakultas_id FK
        string nama
        string kode
    }

    PERUSAHAAN_MITRAS {
        bigint id PK
        bigint user_id FK
        string nama
        text alamat
        string kota
        decimal latitude
        decimal longitude
        decimal radius_valid_meter
        string bidang_industri
        string kontak_person
        string telepon
        string email
        string mitra_jabatan
        time jam_masuk
        time jam_pulang
        int toleransi_terlambat_menit
        json hari_kerja
        boolean is_active
    }

    PEMBIMBING_LAPANGANS {
        bigint id PK
        bigint user_id FK
        bigint perusahaan_id FK
        string jabatan
        boolean is_active
    }

    PENDAFTARAN_MAGANGS {
        bigint id PK
        bigint mahasiswa_id FK
        bigint perusahaan_id FK
        bigint dosen_pembimbing_id FK
        bigint pembimbing_lapangan_id FK
        bigint surat_tugas_id FK
        date tanggal_mulai
        date tanggal_selesai
        string status
        string perusahaan_diminati_nama
        text perusahaan_diminati_alamat
        text catatan_pengajuan
        text catatan_revisi_admin
        string proposal_pkl_path
        string proposal_pkl_original_name
        timestamp proposal_pkl_uploaded_at
        string laporan_akhir_path
        string laporan_akhir_original_name
        timestamp laporan_akhir_uploaded_at
    }

    ABSENSI_MAGANGS {
        bigint id PK
        bigint pendaftaran_id FK
        date tanggal
        time waktu_masuk
        time waktu_keluar
        timestamp timestamp_masuk
        timestamp timestamp_keluar
        decimal latitude_masuk
        decimal longitude_masuk
        decimal latitude_keluar
        decimal longitude_keluar
        decimal distance_masuk
        decimal distance_keluar
        boolean lokasi_valid
        string foto_bukti_path
        string foto_bukti_checkout_path
        string status
        text keterangan
        string ip_address
    }

    LOGBOOK_MAGANGS {
        bigint id PK
        bigint pendaftaran_id FK
        date tanggal
        time jam_mulai
        time jam_selesai
        text aktivitas_harian
        text kompetensi_dicapai
        string status
        text catatan_dosen
        text catatan_mitra
        bigint reviewed_by_mitra_user_id FK
        timestamp reviewed_by_mitra_at
    }

    LOGBOOK_PHOTOS {
        bigint id PK
        bigint logbook_id FK
        string file_path
        timestamp created_at
    }

    KETIDAKHADIRAN_MAGANGS {
        bigint id PK
        bigint pendaftaran_id FK
        bigint mahasiswa_id FK
        bigint perusahaan_id FK
        date tanggal_mulai
        date tanggal_selesai
        string jenis
        text alasan
        string bukti_path
        string status
        bigint reviewed_by_mitra_user_id FK
        timestamp submitted_at
        timestamp reviewed_by_mitra_at
        timestamp cancelled_at
        text catatan_mitra
    }

    HARI_LIBURS {
        bigint id PK
        date tanggal
        string nama
        boolean is_active
    }

    ASSESSMENT_TEMPLATES {
        bigint id PK
        string name
        text description
        string assessor_role
        date periode_mulai
        date periode_selesai
        boolean is_active
        bigint created_by FK
    }

    ASSESSMENT_COMPONENTS {
        bigint id PK
        bigint assessment_template_id FK
        string name
        text description
        decimal weight_percentage
        int sort_order
    }

    ASSESSMENT_SUBMISSIONS {
        bigint id PK
        bigint pendaftaran_magang_id FK
        bigint assessment_template_id FK
        bigint assessor_id FK
        string assessor_role
        decimal total_score
        string status
        text notes
        timestamp submitted_at
    }

    ASSESSMENT_SCORES {
        bigint id PK
        bigint assessment_submission_id FK
        bigint assessment_component_id FK
        decimal score
        decimal weighted_score
        text note
    }

    FINAL_REPORT_TEMPLATES {
        bigint id PK
        string template_type
        string title
        text description
        string file_path
        string original_name
        string mime_type
        bigint file_size
        boolean is_active
        bigint uploaded_by FK
    }

    SURAT_PENETAPANS {
        bigint id PK
        bigint pendaftaran_id FK
        bigint requested_by FK
        string status
        string provider
        string fast_reference_id
        string nomor_surat
        string file_url
        timestamp requested_at
        timestamp generated_at
        text error_message
        json payload_snapshot
    }

    PROGRAM_STUDIS ||--o{ USERS : memiliki
    USERS ||--o| PERUSAHAAN_MITRAS : akun_mitra
    USERS ||--o| PEMBIMBING_LAPANGANS : akun_pembimbing
    USERS ||--o{ PENDAFTARAN_MAGANGS : mahasiswa
    USERS ||--o{ PENDAFTARAN_MAGANGS : dosen_pembimbing
    PERUSAHAAN_MITRAS ||--o{ PEMBIMBING_LAPANGANS : memiliki
    PERUSAHAAN_MITRAS ||--o{ PENDAFTARAN_MAGANGS : lokasi_magang
    PEMBIMBING_LAPANGANS ||--o{ PENDAFTARAN_MAGANGS : membimbing
    PENDAFTARAN_MAGANGS ||--o{ ABSENSI_MAGANGS : memiliki
    PENDAFTARAN_MAGANGS ||--o{ LOGBOOK_MAGANGS : memiliki
    LOGBOOK_MAGANGS ||--o{ LOGBOOK_PHOTOS : memiliki
    USERS ||--o{ LOGBOOK_MAGANGS : reviewer_mitra
    PENDAFTARAN_MAGANGS ||--o{ KETIDAKHADIRAN_MAGANGS : memiliki
    USERS ||--o{ KETIDAKHADIRAN_MAGANGS : mahasiswa
    PERUSAHAAN_MITRAS ||--o{ KETIDAKHADIRAN_MAGANGS : tujuan_review
    USERS ||--o{ KETIDAKHADIRAN_MAGANGS : reviewer_mitra
    USERS ||--o{ ASSESSMENT_TEMPLATES : pembuat
    ASSESSMENT_TEMPLATES ||--o{ ASSESSMENT_COMPONENTS : memiliki
    PENDAFTARAN_MAGANGS ||--o{ ASSESSMENT_SUBMISSIONS : dinilai
    ASSESSMENT_TEMPLATES ||--o{ ASSESSMENT_SUBMISSIONS : dipakai
    USERS ||--o{ ASSESSMENT_SUBMISSIONS : assessor
    ASSESSMENT_SUBMISSIONS ||--o{ ASSESSMENT_SCORES : memiliki
    ASSESSMENT_COMPONENTS ||--o{ ASSESSMENT_SCORES : dinilai
    USERS ||--o{ FINAL_REPORT_TEMPLATES : uploader
    PENDAFTARAN_MAGANGS ||--o| SURAT_PENETAPANS : dokumen_penetapan
    USERS ||--o{ SURAT_PENETAPANS : requester
```

## 3. Class Diagram WIMS

```mermaid
classDiagram
    class User {
        +id
        +program_studi_id
        +name
        +email
        +user_type
        +nomor_induk
        +no_telepon
        +foto_path
        +is_active
        +tanggal_lahir
        +bio
        +linkedin
    }

    class ProgramStudi {
        +id
        +fakultas_id
        +nama
        +kode
    }

    class PerusahaanMitra {
        +id
        +user_id
        +nama
        +alamat
        +kota
        +latitude
        +longitude
        +radius_valid_meter
        +jam_masuk
        +jam_pulang
        +toleransi_terlambat_menit
        +hari_kerja
        +is_active
    }

    class PembimbingLapangan {
        +id
        +user_id
        +perusahaan_id
        +jabatan
        +is_active
    }

    class PendaftaranMagang {
        +id
        +mahasiswa_id
        +perusahaan_id
        +dosen_pembimbing_id
        +pembimbing_lapangan_id
        +surat_tugas_id
        +tanggal_mulai
        +tanggal_selesai
        +status
        +perusahaan_diminati_nama
        +perusahaan_diminati_alamat
        +proposal_pkl_path
        +laporan_akhir_path
    }

    class AbsensiMagang {
        +id
        +pendaftaran_id
        +tanggal
        +timestamp_masuk
        +timestamp_keluar
        +latitude_masuk
        +longitude_masuk
        +latitude_keluar
        +longitude_keluar
        +distance_masuk
        +distance_keluar
        +lokasi_valid
        +status
    }

    class LogbookMagang {
        +id
        +pendaftaran_id
        +tanggal
        +jam_mulai
        +jam_selesai
        +aktivitas_harian
        +kompetensi_dicapai
        +status
        +catatan_dosen
        +catatan_mitra
        +reviewed_by_mitra_user_id
    }

    class LogbookPhoto {
        +id
        +logbook_id
        +file_path
    }

    class KetidakhadiranMagang {
        +id
        +pendaftaran_id
        +mahasiswa_id
        +perusahaan_id
        +tanggal_mulai
        +tanggal_selesai
        +jenis
        +status
        +reviewed_by_mitra_user_id
    }

    class HariLibur {
        +id
        +tanggal
        +nama
        +is_active
    }

    class AssessmentTemplate {
        +id
        +name
        +assessor_role
        +periode_mulai
        +periode_selesai
        +is_active
        +created_by
    }

    class AssessmentComponent {
        +id
        +assessment_template_id
        +name
        +weight_percentage
        +sort_order
    }

    class AssessmentSubmission {
        +id
        +pendaftaran_magang_id
        +assessment_template_id
        +assessor_id
        +assessor_role
        +total_score
        +status
        +submitted_at
    }

    class AssessmentScore {
        +id
        +assessment_submission_id
        +assessment_component_id
        +score
        +weighted_score
    }

    class FinalReportTemplate {
        +id
        +template_type
        +title
        +file_path
        +original_name
        +mime_type
        +file_size
        +is_active
        +uploaded_by
    }

    class SuratPenetapan {
        +id
        +pendaftaran_id
        +requested_by
        +status
        +provider
        +nomor_surat
        +file_url
    }

    ProgramStudi "1" --> "0..*" User : users
    User "1" --> "0..1" PerusahaanMitra : user
    User "1" --> "0..1" PembimbingLapangan : user
    User "1" --> "0..*" PendaftaranMagang : mahasiswa
    User "1" --> "0..*" PendaftaranMagang : dosenPembimbing
    PerusahaanMitra "1" --> "0..*" PembimbingLapangan : pembimbingLapangans
    PerusahaanMitra "1" --> "0..*" PendaftaranMagang : pendaftaranMagangs
    PembimbingLapangan "1" --> "0..*" PendaftaranMagang : pendaftaranMagangs
    PendaftaranMagang "1" --> "0..*" AbsensiMagang : absensis
    PendaftaranMagang "1" --> "0..*" LogbookMagang : logbooks
    LogbookMagang "1" --> "0..*" LogbookPhoto : photos
    User "1" --> "0..*" LogbookMagang : reviewedByMitra
    PendaftaranMagang "1" --> "0..*" KetidakhadiranMagang : absences
    User "1" --> "0..*" KetidakhadiranMagang : mahasiswa
    PerusahaanMitra "1" --> "0..*" KetidakhadiranMagang : perusahaan
    User "1" --> "0..*" KetidakhadiranMagang : reviewedByMitra
    User "1" --> "0..*" AssessmentTemplate : createdBy
    AssessmentTemplate "1" --> "0..*" AssessmentComponent : components
    PendaftaranMagang "1" --> "0..*" AssessmentSubmission : assessmentSubmissions
    AssessmentTemplate "1" --> "0..*" AssessmentSubmission : submissions
    User "1" --> "0..*" AssessmentSubmission : assessor
    AssessmentSubmission "1" --> "0..*" AssessmentScore : scores
    AssessmentComponent "1" --> "0..*" AssessmentScore : scores
    User "1" --> "0..*" FinalReportTemplate : uploadedBy
    PendaftaranMagang "1" --> "0..1" SuratPenetapan : suratPenetapan
    User "1" --> "0..*" SuratPenetapan : requestedBy
```

## 4. Penjelasan Entitas Utama

### `users`

Tabel ini menjadi sumber aktor WIMS:

- mahasiswa
- dosen
- mitra
- admin

Kolom aktif yang paling relevan untuk WIMS:

- `id`
- `program_studi_id`
- `name`
- `email`
- `user_type`
- `nomor_induk`
- `no_telepon`
- `foto_path`
- `is_active`
- `tanggal_lahir`
- `bio`
- `linkedin`

Catatan:

- `nomor_induk` adalah kolom identitas aktif.
- `nim_nip` masih muncul di beberapa fallback kode, tetapi **kolom database-nya sudah di-drop**, jadi tidak dimasukkan ke ERD utama.

### `perusahaan_mitras`

Mewakili perusahaan/instansi mitra tempat mahasiswa magang. Tabel ini juga menyimpan konfigurasi operasional presensi, misalnya:

- koordinat kantor
- radius validasi lokasi
- jam kerja
- toleransi keterlambatan
- hari kerja
- akun user mitra aktif

### `pendaftaran_magangs`

Ini adalah tabel sentral WIMS. Hampir semua proses terhubung ke sini:

- pendaftaran mahasiswa
- penempatan ke mitra
- dosen pembimbing
- pembimbing lapangan
- proposal PKL
- laporan akhir
- absensi
- logbook
- penilaian

### `absensi_magangs`

Menyimpan presensi harian mahasiswa:

- tanggal
- check-in/check-out
- koordinat GPS
- validasi radius
- foto bukti
- status absensi

### `logbook_magangs` dan `logbook_photos`

`logbook_magangs` menyimpan aktivitas harian mahasiswa, sedangkan `logbook_photos` menyimpan lampiran foto aktivitas logbook.

### `ketidakhadiran_magangs`

Menyimpan pengajuan izin/sakit/tidak hadir dari mahasiswa, termasuk:

- rentang tanggal
- alasan
- bukti lampiran
- status review
- reviewer dari pihak mitra

### `assessment_templates`, `assessment_components`, `assessment_submissions`, `assessment_scores`

Ini adalah struktur penilaian aktif WIMS.

Alurnya:

- admin membuat `assessment_templates`
- tiap template memiliki beberapa `assessment_components`
- dosen/mitra mengirim `assessment_submissions`
- setiap submission memiliki detail nilai di `assessment_scores`

Inilah skema penilaian aktif yang dipakai controller/service WIMS, bukan `penilaian_magangs`.

### `final_report_templates`

Menyimpan template dokumen untuk:

- `proposal`
- `final_report`

Kolom `template_type` adalah kolom aktif yang membedakan jenis template.

### `surat_penetapans`

Menyimpan dokumen penetapan penempatan mahasiswa yang dihasilkan dari workflow administratif.

### `hari_liburs`

Dipakai untuk sinkronisasi kehadiran dan monitoring, agar hari libur tidak dianggap alfa atau hari kerja aktif.

## 5. Tabel/Kolom Yang Sengaja Tidak Dijadikan Dasar Diagram Utama

### Tidak dimasukkan sebagai tabel utama

- `penilaian_magangs`
  Alasan: skema penilaian aktif WIMS sudah memakai tabel assessment baru.

- `surats`
  Alasan: ada FK `surat_tugas_id`, tetapi belum menjadi relasi aktif utama dalam alur WIMS saat ini.

### Tidak dimasukkan sebagai kolom aktif

- `users.nim_nip`
  Alasan: sudah dihapus oleh migrasi aktif, walaupun masih ada fallback referensi di sebagian kode.

## 6. Narasi Singkat Untuk Skripsi

Narasi yang bisa dipakai:

> ERD modul WIMS menunjukkan bahwa tabel pusat sistem adalah `pendaftaran_magangs` karena menjadi penghubung antara data mahasiswa, perusahaan mitra, dosen pembimbing, pembimbing lapangan, absensi, logbook, ketidakhadiran, penilaian, laporan akhir, dan dokumen penetapan. Struktur penilaian pada implementasi aktif WIMS tidak lagi bergantung pada tabel penilaian lama, melainkan menggunakan pendekatan template, komponen, submission, dan skor penilaian. Sementara itu, class diagram menggambarkan relasi antar model utama yang dipakai oleh service dan controller modul WIMS dalam mengelola seluruh siklus magang.

## 7. Referensi Implementasi

- `routes/wims.php`
- `app/Models/Magang/`
- `app/Modules/Wims/Services/`
- `database/migrations/magang/`
- `database/migrations/core/`
- `database/migrations/2026_07_01_000000_create_final_report_templates_table.php`
- `database/migrations/2026_07_04_000000_add_template_type_to_final_report_templates_table.php`
