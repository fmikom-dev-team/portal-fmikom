## Template Features Existing

Audit ini membandingkan `develop` dengan `feature-fast` sebagai snapshot FAST lama di repo ini.

- Struktur template surat masih lengkap: `template_header`, `template_body`, dan `template_footer` ada di `SuratTemplate`.
- Renderer surat masih satu jalur untuk preview dan PDF:
  - `app/Modules/Fast/Template/TemplateService.php`
  - `app/Modules/Fast/Template/Renderers/SuratKomponenRenderer.php`
  - `app/Modules/Fast/Services/Shared/SuratDocumentGeneratorService.php`
- Kop surat masih didukung lewat:
  - `kop_html`
  - `logo_path`
  - `logo_kop_position`
  - `font_family_*`
  - `font_size_kop_*`
  - `kop_border_thickness`
- Footer surat masih didukung lewat:
  - `footer_html`
  - `nama_instansi_footer`
  - `alamat_footer`
  - `font_size_footer_*`
  - `footer_border_thickness`
- Margin dan indent masih didukung:
  - `margin_top`
  - `margin_right`
  - `margin_bottom`
  - `margin_left`
  - `body_indent`
  - `paragraph_indent`
  - `table_indent`
- Tanda tangan masih didukung di renderer komponen dan seeder template.
- Nomor surat masih didukung lewat `nomor_surat` dan generator nomor surat.
- QR Code masih didukung di render PDF dan preview final ketika surat selesai/disetujui.
- Preview template masih mengirim `template_header`, `template_body`, `template_footer`, `template_components`, dan `preview_url` ke admin UI.

## Missing Template Features

- Tidak ada kehilangan fitur template surat yang bersifat fungsional antara `develop` dan `feature-fast`.
- Perbedaan pada `app/Models/TemplateGlobalSetting.php` hanya cleanup komentar dan spasi, bukan perubahan perilaku.
- Tidak ada renderer baru atau renderer yang hilang untuk Template Surat.

## Missing Settings

- Tidak ada setting khusus untuk QR Code sebagai kontrol template yang berdiri sendiri.
  - QR saat ini mengikuti status surat dan token QR, bukan toggle template-level.
- Tidak ada setting per-template untuk logo kop.
  - Logo masih dikendalikan oleh setting global `logo_path` dan fallback logo temp.
- Tidak ada setting alias placeholder di level admin untuk memetakan nama placeholder legacy ke nama canonical.

## Missing Placeholders

- Placeholder legacy yang masih dipakai di seeder template, tetapi tidak menjadi placeholder canonical pertama di `SuratDataContract`:
  - `penanda_tangan_jabatan`
  - `penanda_tangan_nama`
  - `penanda_tangan_nidn`
  - `nip`
  - `judul_skripsi`
- Placeholder canonical yang memang tersedia di kontrak sekarang:
  - `nama_penanda_tangan`
  - `email_penanda_tangan`
  - `nik_penanda_tangan`
  - `nomor_induk_penanda_tangan`
  - `jabatan_penanda_tangan`
  - `nama_kaprodi`
  - `nip_kaprodi`
  - `nama_dekan`
  - `nip_dekan`
  - `kop_logo_data_uri`
  - `tanggal_surat_panjang`
  - `semester_terbilang`
  - `kelas_info`
  - `dosen_pengampu_info`
  - `ruang_sidang_info`
- Risiko utama:
  - Template lama yang masih menyimpan nama legacy dapat tampil blank untuk field tertentu jika payload data tidak menyediakan alias yang sama.
  - Ini paling terasa pada template yang masih mengandalkan `penanda_tangan_*`, `nip`, dan `judul_skripsi`.

## Missing Admin Controls

- Tidak ada kontrol admin khusus untuk alias placeholder legacy.
- Tidak ada kontrol admin khusus untuk QR placement/enablement di template editor.
- Tidak ada kontrol admin khusus untuk memilih signatory template secara manual per dokumen; resolusi signer masih berbasis role dan konteks program studi.
- Tidak ada mode migrasi visual yang membedakan template HTML legacy dari template komponen secara eksplisit di UI admin.

## Rendering Differences

- `SuratTemplateRendererService` hanya mewarisi `TemplateService`; tidak ada jalur render template yang berbeda antara branch lama dan sekarang.
- `TemplatePlaceholderSynchronizer` tetap membaca placeholder dari:
  - `template_header`
  - `template_body`
  - `template_footer`
  lalu sinkron ke tabel placeholder.
- Preview dan generate PDF masih memakai pipeline render yang sama, jadi perbedaan visual seharusnya minimal.
- PDF generator masih memakai browser-first lalu fallback ke mPDF.
- QR Code hanya muncul pada surat final atau status yang memenuhi syarat approval.

## Recommendation

- Anggap FAST template surat saat ini sudah setara secara fitur dengan FAST lama.
- Prioritas berikutnya bukan menambah fitur baru, tetapi menutup gap kompatibilitas placeholder legacy.
- Sebelum template lama disimpan ulang, validasi satu per satu apakah masih memakai:
  - `penanda_tangan_*`
  - `nip`
  - `judul_skripsi`
- Kalau ingin aman untuk jangka panjang, tambahkan alias mapping placeholder tanpa mengubah data template lama.
- Jangan mass-rewrite template lama sebelum semua preview dan PDF tervalidasi.
