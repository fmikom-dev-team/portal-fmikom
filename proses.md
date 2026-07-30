# 🚀 Panduan Pasca-Deployment (Post-Deployment Guide) Portal FMIKOM

Dokumen ini berisi panduan langkah demi langkah yang harus dilakukan **setelah kontainer berhasil di-deploy** di VPS Dokploy. Ikuti langkah-langkah ini untuk memastikan database terisi data master, akun admin terbuat, penyimpanan media aman, dan seluruh sistem berjalan **100% Sempurna & Siap Pakai (Production-Ready)**.

---

## 📌 Check-List Utama Pasca-Deployment

- [ ] **Langkah 1**: Migrasi Database & Pengisian Data Master (Seeder)
- [ ] **Langkah 2**: Pembuatan Akun Super Admin Pertama
- [ ] **Langkah 3**: Verifikasi Storage Symlink & Permission Upload File
- [ ] **Langkah 4**: Optimasi & Caching Laravel Production
- [ ] **Langkah 5**: Verifikasi Worker & Process Manager (Supervisor)
- [ ] **Langkah 6**: Pengujian Real-Time WebSockets (Laravel Reverb)
- [ ] **Langkah 7**: Pengujian Login Google OAuth & Email SMTP
- [ ] **Langkah 8**: Monitoring & Pengamanan Sistem (Security Check)

---

## 🛠️ Langkah 1: Migrasi Database & Pengisian Data Master (Seeder)

Meskipun `entrypoint.sh` otomatis menjalankan migrasi, Anda wajib memasukkan **Data Master** (Role, Permission, Modul, Fakultas/Prodi, Wilayah, & Template Surat) agar aplikasi dapat berfungsi normal.

### A. Buka Terminal Kontainer Aplikasi di Dokploy
1. Login ke Dashboard Dokploy ➔ Masuk ke Project **DEVELOPER** ➔ Application **FMIKOM**.
2. Klik tombol **`>_ Open Terminal`** atau masuk via SSH ke VPS Anda:
   ```bash
   docker exec -it <container_id_fmikom_app> sh
   ```

### B. Masuk ke Folder Aplikasi & Jalankan Seeder
Di dalam terminal kontainer, pindah ke folder aplikasi `/var/www/html` lalu jalankan seeder:

```bash
cd /var/www/html
php artisan db:seed --force
```

Perintah di atas akan secara otomatis menginisialisasi:
- **Permission & Role**: `PermissionSeeder` (Menyusun role Admin, Dosen, Mahasiswa, Alumni, dll).
- **Modul Sistem**: `ModuleSeeder` (Mengaktifkan modul WorkOS, PAGI, WIMS, TRACE, FAST).
- **Fakultas & Prodi**: `FakultasProdiSeeder` (Master data akademik FMIKOM).
- **Data Wilayah**: `ProvinsiSeeder` & `KotaSeeder` (Wilayah Indonesia).
- **Kategori & Template Surat**: `SuratCategorySeeder`, `JenisSuratSeeder`, & `SuratTemplateSeeder`.
- **Halaman Portal Statis**: `PortalPagesSeeder` (Mengisi 22 halaman portal publik: Profil, Akademik, Berita & Media, Layanan).
- **Kategori Kerja & Provider**: `JobCategorySeeder`, `PipeProviderSeeder`, & `RadarSeeder`.

### C. (Opsional) Mengisi Data Dummy/Uji Coba
Jika Anda membutuhkan data dummy untuk pengujian awal di staging:
```bash
php artisan db:seed --class=PlaywrightSeeder --force
```

---

## 👑 Langkah 2: Akun Super Admin Bawaan & Pembuatan Akun Kustom (Opsional)

### A. Akun Super Admin Bawaan (Otomatis Terbuat dari Seeder)
Saat Anda menjalankan `php artisan db:seed --force`, **`ModuleSeeder` sudah otomatis membuat 2 akun Super Admin bawaan**:

| Nama | Email | Password Default | Role |
| :--- | :--- | :--- | :--- |
| **Muchlisin Maruf** | `muchlisinmaruf@gmail.com` | `password123` | Super Admin |
| **Super Admin Test** | `superadmin@test.com` | `superadmin2026` | Super Admin |

> ⚠️ **Saran Keamanan**: Setelah login pertama kali di production, segera ubah password akun-akun bawaan di atas melalui dashboard aplikasi!

---

### B. (Opsional) Pembuatan Akun Super Admin Baru Kustom via Tinker
Jika Anda ingin membuat akun admin kustom baru dengan email Anda sendiri:

1. Di dalam terminal kontainer (`cd /var/www/html`), jalankan:
   ```bash
   php artisan tinker
   ```

2. Salin & jalankan skrip berikut di prompt Tinker:
   ```php
   $user = \App\Models\User::create([
       'name' => 'Admin Utama FMIKOM',
       'email' => 'admin@fmikom.id', // Ganti dengan email Anda
       'password' => \Illuminate\Support\Facades\Hash::make('PasswordAman2026!'),
       'user_type' => 'super-admin',
       'is_active' => true,
       'email_verified_at' => now(),
   ]);

   echo "Akun Admin Kustom Berhasil Dibuat!";
   exit;
   ```

---

## 💾 Langkah 3: Verifikasi Storage Symlink & Permission File Upload

Aplikasi menyimpan media (pasfoto, sertifikat, poster event, dokumen surat) di folder `storage/app/public`.

### A. Verifikasi Storage Link
Jalankan perintah ini di dalam kontainer untuk memastikan tautan publik aktif:

```bash
php artisan storage:link --force
```

### B. Atur Permission Folder Storage
Jika ada masalah thumbnail/gambar tidak muncul, perbarui izin akses direktori:

```bash
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### C. Pengujian Upload File
1. Buka browser ➔ Login ke aplikasi `https://dev.oemah.web.id`.
2. Coba upload pasfoto profil atau gambar postingan.
3. Buka URL gambar tersebut di tab baru (contoh: `https://dev.oemah.web.id/storage/avatars/example.jpg`). Pastikan muncul tanpa error 404/403.

---

## ⚡ Langkah 4: Optimasi & Caching Laravel Production

Untuk mempercepat waktu respon (*load time*) hingga 3x-5x lebih cepat, jalankan seluruh rangkaian perintah caching produksi Laravel:

```bash
# Clear cache lama terlebih dahulu
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Generasi cache produksi baru
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

> ⚠️ **Catatan**: Setiap kali Anda mengubah `.env` di Dokploy, Anda wajib menjalankan `php artisan config:cache` lagi.

---

## ⚙️ Langkah 5: Verifikasi Worker & Process Manager (Supervisor)

Sistem menggunakan **Supervisor** untuk menjalankan 6 latar belakang proses (*background jobs*).

### Cek Status Supervisor
Di dalam terminal kontainer, jalankan:

```bash
supervisorctl status
```

**Output yang WAJIB Berstatus `RUNNING`**:
```text
laravel-cron                     RUNNING   pid 12, uptime 0:10:00
laravel-pulse-check              RUNNING   pid 14, uptime 0:10:00
laravel-pulse-work               RUNNING   pid 13, uptime 0:10:00
laravel-reverb                   RUNNING   pid 11, uptime 0:10:00
laravel-worker                   RUNNING   pid 10, uptime 0:10:00
nginx                            RUNNING   pid 8,  uptime 0:10:00
php-fpm                          RUNNING   pid 9,  uptime 0:10:00
```

- Jika ada service yang `FATAL` atau `STOPPED`, jalankan: `supervisorctl restart all`.

---

## 📡 Langkah 6: Pengujian Real-Time WebSockets (Laravel Reverb)

Proyek ini menggunakan **Laravel Reverb** untuk notifikasi langsung dan fitur real-time.

1. Buka browser ➔ Tekan `F12` (Developer Tools) ➔ Buka tab **Console**.
2. Masuk ke halaman aplikasi `https://dev.oemah.web.id`.
3. Pastikan **tidak ada error WebSocket** seperti `WebSocket connection to 'wss://reverb.oemah.web.id' failed`.
4. Jika terkoneksi dengan benar, di console akan terlihat koneksi WebSocket aman (`wss://`) sukses ke port 443.

---

## 🔑 Langkah 7: Verifikasi Login Google OAuth & Email SMTP

### A. Verifikasi Google OAuth
1. Buka halaman login `https://dev.oemah.web.id/login`.
2. Klik tombol **Login dengan Google**.
3. Pastikan Anda diarahkan ke halaman izin Google dan kembali ke portal dengan status terautentikasi.
4. *Penting*: Pastikan URL Callback berikut sudah didaftarkan di **Google Cloud Console ➔ Credentials**:
   `https://dev.oemah.web.id/auth/oauth/google/callback`

### B. Verifikasi Pengiriman Email (SMTP)
Jalankan perintah pengujian email via Tinker:

```bash
php artisan tinker
```
```php
Mail::raw('Tes Pengiriman Email Production Portal FMIKOM', function ($message) {
    $message->to('email_anda@gmail.com')->subject('Tes SMTP Portal FMIKOM');
});
```

---

## 🛡️ Langkah 8: Monitoring & Pengamanan Sistem (Security Check)

### A. Cek Pengaturan Keamanan `.env`
Pastikan variabel sensitif di DokployEnvironment sudah terpasang aman:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_ENCRYPT=true`
- `TELESCOPE_ENABLED=false`

### B. Akses Dashboard Monitoring (Laravel Pulse)
Buka URL Dashboard Monitoring bawaan aplikasi:
`https://dev.oemah.web.id/secure-pulse-9a2c`

Di dashboard ini Anda dapat memantau:
- Penggunaan CPU & RAM VPS secara real-time.
- Antrean Queue Worker (*Job Processing*).
- Request lambat & Query SQL lambat.

---

## 🎉 Kesimpulan

Setelah seluruh langkah di atas selesai dijalankan, proyek **Portal FMIKOM** Anda telah resmi **100% Deployment Sempurna, Terisolasi Aman, dan Ready Production!** 🚀
