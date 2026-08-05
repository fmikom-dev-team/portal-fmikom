# WIMS User Stories and Product Backlog

Dokumen ini disusun berdasarkan implementasi WIMS di repository lokal saat ini. Karena ada PR WIMS yang belum di-merge, daftar berikut merepresentasikan backlog operasional lokal dan dapat berubah sebelum rilis final.

## 1. Tujuan Dokumen

- mendefinisikan kebutuhan pengguna WIMS dalam bentuk user story
- merangkum prioritas pengerjaan produk
- menjadi bahan diskusi untuk scrum master, product owner, dan tim pengembang

## 2. Persona Utama

- Mahasiswa
- Dosen
- Mitra
- Admin

## 3. Epics

- Epic A: Akses Modul dan Profil
- Epic B: Pendaftaran dan Penempatan
- Epic C: Operasional Harian Magang
- Epic D: Monitoring, Penilaian, dan Laporan
- Epic E: Administrasi dan Konfigurasi Modul

## 4. User Stories

### Epic A: Akses Modul dan Profil

| ID | User Story | Prioritas | Acceptance Criteria Ringkas |
|---|---|---:|---|
| US-01 | Sebagai pengguna portal, saya ingin masuk ke modul WIMS sesuai role aktif saya agar saya hanya melihat menu yang relevan. | High | Pengguna diarahkan ke dashboard peran yang sesuai; akses ditolak jika role tidak valid. |
| US-02 | Sebagai mahasiswa, saya ingin melihat dan memperbarui profil saya agar data magang saya akurat. | High | Halaman profil dapat dibuka dan data dapat disimpan kembali tanpa error validasi. |
| US-03 | Sebagai pengguna WIMS, saya ingin melihat dashboard sesuai peran agar saya mengetahui status dan tugas utama saya. | High | Dashboard menampilkan halaman berbeda untuk mahasiswa, dosen, mitra, dan admin. |

### Epic B: Pendaftaran dan Penempatan

| ID | User Story | Prioritas | Acceptance Criteria Ringkas |
|---|---|---:|---|
| US-04 | Sebagai mahasiswa, saya ingin mendaftar magang melalui WIMS agar pengajuan saya tercatat secara resmi. | High | Form pendaftaran dapat disimpan dan status pendaftaran terbentuk. |
| US-05 | Sebagai mahasiswa, saya ingin mengunduh template proposal agar saya dapat melengkapi berkas pendaftaran. | Medium | File template dapat diunduh dari halaman pendaftaran. |
| US-06 | Sebagai admin, saya ingin memverifikasi pendaftaran mahasiswa agar hanya pengajuan yang layak yang diproses. | High | Admin dapat melihat daftar pendaftaran dan mengubah statusnya. |
| US-07 | Sebagai admin, saya ingin menempatkan mahasiswa ke mitra agar proses magang memiliki lokasi penempatan yang jelas. | High | Admin dapat membuat dan memperbarui data penempatan. |
| US-08 | Sebagai admin, saya ingin mengelola data perusahaan mitra agar mitra aktif dan akun mitra dapat diadministrasikan. | High | Admin dapat membuat, memperbarui, menghapus perusahaan, dan membuat akun terkait. |

### Epic C: Operasional Harian Magang

| ID | User Story | Prioritas | Acceptance Criteria Ringkas |
|---|---|---:|---|
| US-09 | Sebagai mahasiswa, saya ingin melakukan absensi masuk agar kehadiran saya tercatat. | High | Absensi masuk tersimpan dan terasosiasi ke periode aktif. |
| US-10 | Sebagai mahasiswa, saya ingin melakukan checkout absensi agar durasi aktivitas harian saya tercatat. | High | Checkout dapat disimpan setelah absensi masuk. |
| US-11 | Sebagai mahasiswa, saya ingin melihat dan mengunduh riwayat absensi agar saya dapat memeriksa catatan kehadiran saya. | Medium | Riwayat absensi dapat ditampilkan dan diekspor. |
| US-12 | Sebagai mahasiswa, saya ingin menulis logbook harian agar progres kerja saya terdokumentasi. | High | Logbook dapat dibuat dan diperbarui pada periode aktif. |
| US-13 | Sebagai mahasiswa, saya ingin mengunduh logbook periode berjalan agar saya dapat menyimpan arsip kegiatan. | Medium | Logbook periode aktif dapat diunduh. |
| US-14 | Sebagai mahasiswa, saya ingin mengajukan ketidakhadiran agar status absen saya tercatat secara formal. | Medium | Pengajuan ketidakhadiran dapat dibuat dan dihapus sesuai aturan yang berlaku. |
| US-15 | Sebagai mitra, saya ingin meninjau logbook mahasiswa agar saya dapat memantau progres kerja mereka. | High | Mitra dapat membuka detail logbook dan memberi review. |
| US-16 | Sebagai mitra, saya ingin menyetujui atau menolak ketidakhadiran agar status absensi mahasiswa menjadi jelas. | Medium | Mitra dapat memberikan keputusan pada pengajuan ketidakhadiran. |

### Epic D: Monitoring, Penilaian, dan Laporan

| ID | User Story | Prioritas | Acceptance Criteria Ringkas |
|---|---|---:|---|
| US-17 | Sebagai dosen, saya ingin memantau mahasiswa bimbingan agar saya mengetahui perkembangan magang mereka. | High | Dosen dapat melihat daftar dan detail monitoring mahasiswa. |
| US-18 | Sebagai mitra, saya ingin memantau mahasiswa yang ditempatkan agar saya dapat memastikan aktivitas berjalan baik. | High | Mitra dapat melihat ringkasan monitoring dan detail mahasiswa. |
| US-19 | Sebagai dosen, saya ingin memberi penilaian mahasiswa agar hasil magang dapat dievaluasi. | High | Penilaian dapat disimpan untuk pendaftaran yang aktif. |
| US-20 | Sebagai mitra, saya ingin memberi penilaian mahasiswa agar penilaian lapangan tersedia dalam rekap akhir. | High | Penilaian dari mitra dapat disimpan dan diakses pada rekap. |
| US-21 | Sebagai mahasiswa, saya ingin mengunggah laporan akhir agar saya dapat menyelesaikan kewajiban magang. | High | Laporan akhir dapat diunggah dan divalidasi. |
| US-22 | Sebagai mahasiswa, saya ingin melihat dan mengunduh laporan akhir saya agar saya dapat memeriksa file yang tersimpan. | Medium | Laporan dapat dilihat dan diunduh jika tersedia. |
| US-23 | Sebagai dosen atau mitra, saya ingin melihat laporan akhir mahasiswa agar saya dapat menilai hasil kerja secara lengkap. | Medium | Laporan akhir dapat dibuka dan diunduh dari halaman penilaian. |

### Epic E: Administrasi dan Konfigurasi Modul

| ID | User Story | Prioritas | Acceptance Criteria Ringkas |
|---|---|---:|---|
| US-24 | Sebagai admin, saya ingin melihat monitoring keseluruhan agar saya dapat mengawasi status seluruh peserta magang. | High | Admin dapat membuka halaman monitoring modul. |
| US-25 | Sebagai admin, saya ingin mengelola template penilaian agar format evaluasi seragam. | High | Template dapat dibuat, diperbarui, dan dihapus. |
| US-26 | Sebagai admin, saya ingin mengelola template laporan akhir agar mahasiswa memakai format yang sama. | High | Template dapat diunggah, diubah, diunduh, dan dihapus. |
| US-27 | Sebagai admin, saya ingin mengunduh rekap penilaian agar hasil magang mudah direkap. | High | Rekap nilai dapat diunduh per pendaftaran dan per role penilai. |
| US-28 | Sebagai admin, saya ingin mengaktifkan atau menyelesaikan penempatan mahasiswa agar status penempatan tidak ambigu. | High | Penempatan dapat diaktifkan dan ditandai selesai. |

## 5. Product Backlog

Prioritas:
- P0 = harus ada untuk operasi inti
- P1 = penting, sebaiknya masuk rilis berikutnya
- P2 = peningkatan setelah alur inti stabil

| Rank | Backlog Item | Epic | Prioritas | Ketergantungan |
|---|---|---|---:|---|
| 1 | Modul akses dan redirect per role | A | P0 | Konteks role aktif dari portal |
| 2 | Profil mahasiswa | A | P0 | Autentikasi portal |
| 3 | Pendaftaran magang mahasiswa | B | P0 | Profil mahasiswa |
| 4 | Verifikasi pendaftaran oleh admin | B | P0 | Data pendaftaran |
| 5 | Manajemen perusahaan mitra | B | P0 | Hak akses admin |
| 6 | Penempatan mahasiswa ke mitra | B | P0 | Pendaftaran tervalidasi |
| 7 | Absensi masuk dan checkout | C | P0 | Periode aktif dan penempatan |
| 8 | Logbook harian mahasiswa | C | P0 | Periode aktif |
| 9 | Review logbook oleh mitra | C | P0 | Logbook tersimpan |
| 10 | Pengajuan dan review ketidakhadiran | C | P1 | Aturan absensi aktif |
| 11 | Monitoring mahasiswa oleh dosen | D | P0 | Penempatan aktif |
| 12 | Monitoring mahasiswa oleh mitra | D | P0 | Penempatan aktif |
| 13 | Penilaian mahasiswa oleh dosen | D | P0 | Penempatan aktif |
| 14 | Penilaian mahasiswa oleh mitra | D | P0 | Penempatan aktif |
| 15 | Upload dan akses laporan akhir | D | P0 | Template laporan aktif |
| 16 | Template penilaian | E | P1 | Hak akses admin |
| 17 | Template laporan akhir | E | P1 | Hak akses admin |
| 18 | Rekap nilai dan unduhan hasil | E | P1 | Data penilaian tersedia |
| 19 | Bulk approve pendaftaran | E | P2 | Data pendaftaran lengkap |
| 20 | Bulk completion penempatan | E | P2 | Data penempatan valid |

## 6. MVP Recommendation

Untuk rilis minimum yang layak operasional, backlog P0 berikut paling penting:
- akses modul dan redirect per role
- profil mahasiswa
- pendaftaran magang
- verifikasi pendaftaran
- manajemen perusahaan mitra
- penempatan mahasiswa
- absensi dan checkout
- logbook mahasiswa
- review logbook oleh mitra
- monitoring oleh dosen dan mitra
- penilaian dosen dan mitra
- upload laporan akhir

## 7. Delivery Notes

- User story di atas dapat langsung dipindahkan ke Jira sebagai backlog item.
- Jika diperlukan sprint planning, backlog P0 dapat dipecah lagi menjadi task frontend, backend, validation, dan testing.
- Karena ada PR yang belum di-merge, prioritas akhir tetap perlu diselaraskan dengan perubahan terakhir di branch lokal.

