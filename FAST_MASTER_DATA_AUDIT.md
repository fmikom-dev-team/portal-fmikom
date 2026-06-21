# FAST Master Data Audit

Audit scope: kategori surat, jenis surat, dan template surat default FAST setelah merge ke `develop`.

## 1. Ringkasan Temuan

- Data master FAST **tidak** dibuat oleh migration.
- Data master FAST default **sebelumnya hanya berasal dari seeder** di branch `feature-fast`.
- Di `develop` sebelum perbaikan ini, tiga seeder default FAST hilang:
  - `database/seeders/SuratCategorySeeder.php`
  - `database/seeders/JenisSuratSeeder.php`
  - `database/seeders/SuratTemplateSeeder.php`
- Admin UI FAST memang tersedia untuk mengelola kategori dan template secara manual, tetapi UI tersebut **bukan sumber bootstrap data default**.
- Karena seeder masih menjadi satu-satunya sumber data default, saya restore hanya tiga seeder FAST tersebut.

## 2. Jawaban Audit

### 2.1 Apakah data master tersebut sekarang dibuat oleh migration?

Tidak.

Migration FAST yang relevan hanya menambah struktur tabel dan beberapa field pendukung. Migration tidak mengisi data default untuk:

- `surat_categories`
- `jenis_surats`
- `surat_templates`

Yang ada hanya seed default untuk `template_global_settings` pada migration `2026_05_03_000001_enhance_surat_system.php`.

### 2.2 Apakah hanya tersedia melalui seeder?

Ya, untuk data master default.

Di branch `feature-fast`, default data FAST dibuat melalui:

- `SuratCategorySeeder`
- `JenisSuratSeeder`
- `SuratTemplateSeeder`

Seeder ini mengisi kategori, jenis surat, template, dan placeholder template.

### 2.3 Apakah harus dibuat manual lewat Admin UI?

Tidak harus, tetapi bisa.

Admin UI FAST tersedia untuk pengelolaan data setelah sistem berjalan. Namun UI tersebut tidak menggantikan bootstrap data default saat install baru.

### 2.4 Kesimpulan

Jika targetnya fresh install yang langsung memiliki master FAST default, maka seeder tetap dibutuhkan.

## 3. Missing Files

File FAST default yang hilang dari `develop` sebelum restore:

- `database/seeders/SuratCategorySeeder.php`
- `database/seeders/JenisSuratSeeder.php`
- `database/seeders/SuratTemplateSeeder.php`

File tersebut sekarang sudah saya restore.

## 4. Missing Seeders

Seeder default FAST yang sempat hilang:

- `SuratCategorySeeder`
- `JenisSuratSeeder`
- `SuratTemplateSeeder`

Seeder yang **tidak** saya restore sesuai instruksi:

- `UserSeeder`
- `RoleSeeder`

## 5. Missing Configs

Tidak ditemukan config FAST baru yang wajib untuk bootstrap master data.

Catatan:

- `template_global_settings` sudah ditanam dari migration.
- `kaprodi` dan `dekan` dipakai oleh beberapa jenis surat FAST, tetapi role tersebut tidak dibuat oleh seed yang ada di `develop`.

## 6. Missing Assets

Tidak ada asset FAST master-data yang hilang pada audit ini.

Template surat memakai konten HTML inline dan placeholder, bukan asset terpisah yang wajib ada untuk bootstrap data.

## 7. Migration Issues

Tidak ada migration yang menanam master data default FAST.

Artinya:

- fresh install tetap membentuk tabel FAST,
- tetapi tanpa seeder yang benar, kategori/jenis/template default tidak akan ada,
- dan fitur FAST yang bergantung pada data itu akan tampak kosong.

## 8. Deployment Risk

Risiko deployment utama:

1. `php artisan db:seed` tanpa seeder FAST default akan menghasilkan modul FAST tanpa master data awal.
2. Approval flow pada beberapa jenis surat bisa tidak lengkap jika role `kaprodi` dan `dekan` tidak ada di database.
3. Frontend dan backend FAST akan tetap build, tetapi data master default tidak tersedia untuk penggunaan pertama.

## 9. Recommended Fixes

1. Pertahankan tiga seeder FAST:
   - `SuratCategorySeeder`
   - `JenisSuratSeeder`
   - `SuratTemplateSeeder`
2. Jangan restore `UserSeeder` dan `RoleSeeder` bila scope hanya FAST.
3. Pastikan `DatabaseSeeder` memanggil tiga seeder FAST tersebut.
4. Jika deployment fresh install harus langsung lengkap, siapkan dokumentasi bahwa admin masih bisa mengubah data lewat Admin UI setelah bootstrap awal.
5. Jika approval role `kaprodi` / `dekan` memang dibutuhkan di production, tambahkan strategi bootstrap role yang eksplisit di luar audit FAST ini.

## 10. Status Akhir

Hasil audit:

- Data master FAST default **masih perlu seeder**
- Migration **bukan** sumber bootstrap data master
- Admin UI **hanya alat pengelolaan**, bukan bootstrap
- Tiga seeder FAST default telah dipulihkan
