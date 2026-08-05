# Product Backlog Modul WIMS

Dokumen ini memuat product backlog untuk modul **WIMS (Web-based Internship Management System)**. Backlog disusun berdasarkan ruang lingkup fitur modul WIMS pada repository saat ini dan ditujukan untuk kebutuhan penulisan skripsi, khususnya pada bagian perencanaan produk berbasis Scrum.

## Tujuan Product Backlog

Product backlog digunakan sebagai daftar kebutuhan sistem yang telah diprioritaskan dan akan menjadi acuan dalam proses pengembangan modul WIMS. Setiap backlog item merepresentasikan fitur atau fungsi yang memberikan nilai bagi pengguna sistem.

## Aktor Sistem

- Mahasiswa
- Dosen
- Mitra
- Admin
- Sistem

## Daftar Product Backlog

| ID | Epic | Product Backlog Item | Aktor | Prioritas | Deskripsi Singkat |
|---|---|---|---|---|---|
| PB-01 | Akses dan Otorisasi | Login dan akses modul sesuai role | Semua pengguna | Tinggi | Sistem mengarahkan pengguna ke dashboard WIMS sesuai peran aktif, yaitu mahasiswa, dosen, mitra, atau admin. |
| PB-02 | Akses dan Otorisasi | Dashboard berdasarkan peran | Semua pengguna | Tinggi | Sistem menampilkan ringkasan informasi dan menu utama yang berbeda untuk setiap role. |
| PB-03 | Profil Mahasiswa | Kelola profil mahasiswa | Mahasiswa | Tinggi | Mahasiswa dapat melihat dan memperbarui data profil yang digunakan dalam proses magang. |
| PB-04 | Pendaftaran Magang | Pengajuan pendaftaran magang | Mahasiswa | Tinggi | Mahasiswa dapat mengisi data pendaftaran magang beserta informasi instansi tujuan. |
| PB-05 | Pendaftaran Magang | Unggah proposal pendaftaran | Mahasiswa | Tinggi | Mahasiswa dapat mengunggah dokumen proposal sebagai syarat pengajuan magang. |
| PB-06 | Pendaftaran Magang | Unduh template proposal | Mahasiswa | Sedang | Mahasiswa dapat mengunduh template proposal yang disediakan sistem. |
| PB-07 | Validasi Pendaftaran | Verifikasi dan persetujuan pendaftaran | Admin | Tinggi | Admin dapat meninjau, menerima, atau menolak pengajuan pendaftaran mahasiswa. |
| PB-08 | Validasi Pendaftaran | Persetujuan massal pendaftaran | Admin | Sedang | Admin dapat melakukan persetujuan beberapa pengajuan sekaligus untuk meningkatkan efisiensi proses. |
| PB-09 | Penempatan Magang | Kelola data perusahaan mitra | Admin | Tinggi | Admin dapat menambah, mengubah, dan menghapus data perusahaan mitra magang. |
| PB-10 | Penempatan Magang | Pembuatan akun mitra perusahaan | Admin | Sedang | Admin dapat membuat akun pengguna mitra agar pihak perusahaan dapat mengakses sistem. |
| PB-11 | Penempatan Magang | Penetapan dosen pembimbing dan mitra | Admin | Tinggi | Admin dapat menentukan dosen pembimbing dan perusahaan mitra untuk mahasiswa yang lolos pendaftaran. |
| PB-12 | Penempatan Magang | Aktivasi penempatan mahasiswa | Admin | Tinggi | Admin dapat mengaktifkan status penempatan agar mahasiswa dapat memulai kegiatan magang. |
| PB-13 | Penempatan Magang | Penyelesaian penempatan | Admin | Sedang | Admin dapat menandai kegiatan magang mahasiswa sebagai selesai, baik secara individual maupun massal. |
| PB-14 | Presensi | Input presensi harian | Mahasiswa | Tinggi | Mahasiswa dapat melakukan check-in kehadiran harian selama periode magang. |
| PB-15 | Presensi | Check-out presensi | Mahasiswa | Tinggi | Mahasiswa dapat melakukan check-out sebagai penanda selesai aktivitas harian. |
| PB-16 | Presensi | Riwayat dan unduh presensi | Mahasiswa | Sedang | Mahasiswa dapat melihat serta mengunduh riwayat presensi selama magang. |
| PB-17 | Ketidakhadiran | Pengajuan izin atau ketidakhadiran | Mahasiswa | Tinggi | Mahasiswa dapat mengajukan izin tidak hadir disertai alasan atau bukti pendukung. |
| PB-18 | Ketidakhadiran | Persetujuan atau penolakan izin | Mitra | Tinggi | Mitra dapat meninjau dan memberikan keputusan atas pengajuan ketidakhadiran mahasiswa. |
| PB-19 | Logbook | Input logbook kegiatan harian | Mahasiswa | Tinggi | Mahasiswa dapat mencatat aktivitas harian magang dalam logbook. |
| PB-20 | Logbook | Edit logbook | Mahasiswa | Sedang | Mahasiswa dapat memperbarui catatan logbook apabila terdapat kesalahan input. |
| PB-21 | Logbook | Review logbook mahasiswa | Mitra | Tinggi | Mitra dapat meninjau dan memberikan review terhadap logbook mahasiswa. |
| PB-22 | Logbook | Unduh logbook | Mahasiswa, Admin | Sedang | Sistem menyediakan fitur unduh logbook untuk kebutuhan monitoring dan dokumentasi. |
| PB-23 | Monitoring | Monitoring mahasiswa oleh dosen | Dosen | Tinggi | Dosen dapat melihat perkembangan mahasiswa bimbingannya berdasarkan data presensi, logbook, dan status magang. |
| PB-24 | Monitoring | Monitoring mahasiswa oleh mitra | Mitra | Tinggi | Mitra dapat memantau aktivitas mahasiswa yang ditempatkan di instansinya. |
| PB-25 | Monitoring | Monitoring terpusat oleh admin | Admin | Tinggi | Admin dapat melihat rekap monitoring seluruh mahasiswa magang. |
| PB-26 | Monitoring | Detail monitoring mahasiswa | Dosen, Mitra, Admin | Tinggi | Sistem menampilkan detail progres mahasiswa, termasuk kehadiran, logbook, dan dokumen terkait. |
| PB-27 | Monitoring | Unduh data monitoring | Admin | Sedang | Admin dapat mengunduh data presensi dan logbook mahasiswa untuk keperluan evaluasi. |
| PB-28 | Laporan Akhir | Unggah laporan akhir | Mahasiswa | Tinggi | Mahasiswa dapat mengunggah dokumen laporan akhir magang. |
| PB-29 | Laporan Akhir | Lihat dan unduh laporan akhir | Mahasiswa, Dosen, Mitra | Tinggi | Sistem memungkinkan laporan akhir ditinjau langsung atau diunduh oleh pihak terkait. |
| PB-30 | Laporan Akhir | Unduh template laporan akhir | Mahasiswa | Sedang | Mahasiswa dapat mengunduh template laporan akhir yang telah disediakan admin. |
| PB-31 | Template Dokumen | Kelola template proposal/laporan | Admin | Sedang | Admin dapat menambah, memperbarui, dan menghapus template dokumen resmi WIMS. |
| PB-32 | Penilaian | Penilaian mahasiswa oleh dosen | Dosen | Tinggi | Dosen dapat mengisi penilaian mahasiswa berdasarkan template penilaian yang tersedia. |
| PB-33 | Penilaian | Penilaian mahasiswa oleh mitra | Mitra | Tinggi | Mitra dapat mengisi penilaian performa mahasiswa selama magang. |
| PB-34 | Penilaian | Kelola template penilaian | Admin | Sedang | Admin dapat membuat dan mengelola instrumen penilaian untuk dosen dan mitra. |
| PB-35 | Penilaian | Rekap nilai mahasiswa | Admin | Tinggi | Admin dapat melihat rekap hasil penilaian mahasiswa dari berbagai penilai. |
| PB-36 | Penilaian | Unduh rekap nilai | Admin | Sedang | Admin dapat mengunduh hasil rekap nilai sebagai dokumen evaluasi resmi. |
| PB-37 | Keamanan Dokumen | Pengelolaan file secara aman | Sistem/Admin | Sedang | Dokumen WIMS seperti proposal dan laporan akhir disimpan serta diakses secara terkontrol. |
| PB-38 | Integrasi Portal | Integrasi role dengan portal utama | Sistem | Tinggi | Hak akses WIMS mengikuti role pengguna yang aktif pada portal utama kampus. |

## Ringkasan Prioritas

### Prioritas Tinggi

- akses modul berdasarkan role
- dashboard per role
- pengelolaan profil mahasiswa
- pendaftaran magang
- verifikasi pendaftaran
- penempatan mahasiswa
- presensi harian
- pengajuan ketidakhadiran
- logbook kegiatan
- monitoring mahasiswa
- unggah laporan akhir
- penilaian mahasiswa
- rekap nilai

### Prioritas Sedang

- unduh template proposal
- persetujuan massal pendaftaran
- pembuatan akun mitra
- unduh riwayat presensi
- edit dan unduh logbook
- unduh data monitoring
- unduh template laporan akhir
- pengelolaan template dokumen
- pengelolaan template penilaian
- unduh rekap nilai
- pengelolaan file secara aman

## Penutup

Product backlog ini dapat digunakan sebagai dasar penyusunan sprint backlog, user story, maupun pembahasan hasil analisis kebutuhan pada skripsi. Apabila diperlukan, setiap item backlog selanjutnya dapat diuraikan lebih detail ke dalam format user story dan acceptance criteria.
