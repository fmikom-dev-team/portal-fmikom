# 📘 Panduan Lengkap Deployment Portal FMIKOM ke VPS via Dokploy

Dokumen ini berisi panduan langkah demi langkah yang terstruktur, rinci, dan lengkap untuk melakukan deployment proyek **Portal FMIKOM** ke Server VPS menggunakan **Dokploy** (Platform-as-a-Service / PaaS self-hosted berbasis Docker & Traefik).

---

## 📐 1. Gambaran Arsitektur & Perangkat Lunak

Aplikasi Portal FMIKOM dibangun dengan arsitektur **Modular Monolith** berbasis stack berikut:
- **Backend Framework**: Laravel 12 (PHP 8.4-FPM Alpine)
- **Frontend Framework**: Inertia.js + Vue 3 + Vite
- **Database**: MySQL 8.0 / 8.4
- **In-Memory Cache / Queue / Session**: Redis Alpine
- **Realtime WebSockets**: Laravel Reverb (Port 8080 internal)
- **Process Manager & Web Server**: Supervisor + Nginx + Crond (Laravel Scheduler) + Laravel Worker + Laravel Pulse
- **Multimedia Utility**: FFmpeg & FFprobe (Terbawa bawaan di image Docker)

Seluruh komponen ini disatukan dalam **Multi-Stage Dockerfile** yang sangat dioptimalkan:
1. **Stage 1 (Composer Builder)**: Menginstall dependency PHP tanpa dev & menggenerasi rute Wayfinder.
2. **Stage 2 (Frontend Builder)**: Mengkompilasi asset JS/CSS menggunakan Node.js 22 Alpine & Vite.
3. **Stage 3 (Production Runtime)**: Menggabungkan artefak PHP & JS ke dalam kontainer ringan Alpine yang dikelola oleh **Supervisor**.

---

## 📋 2. Prasyarat Deployment (Prerequisites)

Sebelum memulai proses deployment di Dokploy, pastikan Anda telah menyiapkan hal-hal berikut:

### A. Spesifikasi Minimum VPS
- **OS**: Ubuntu 22.04 LTS / 24.04 LTS atau Debian 12.
- **CPU**: Minimum 2 vCPU Core (Disarankan 4 Core untuk performa antrean & websocket yang lancar).
- **RAM**: Minimum 2 GB (Disarankan 4 GB + Swap 2 GB agar kompilasi Docker lancar).
- **Storage**: Minimum 20 GB SSD / NVMe.

### B. Dokploy & Domain Setup
1. **Dokploy** sudah terinstall di VPS Anda. (Jika belum, install via `curl -sSL https://dokploy.com/install.sh | sh`).
2. **Domain / Subdomain** sudah diarahkan (A Record DNS) ke IP Public VPS Anda.
   - Contoh A Record: `fmikom.domainanda.com` ➔ `103.xxx.xxx.xxx`
3. Repository GitHub proyek ini terhubung ke Dokploy.

---

## 🛠️ 3. Pilihan Metode Deployment di Dokploy

Dokploy mendukung 2 cara deployment utama. Pilihlah salah satu yang sesuai dengan kebutuhan infrastructure Anda:

| Fitur | Opsi A: Docker Compose (Direkomendasikan) | Opsi B: Dokploy App + Managed DB |
| :--- | :--- | :--- |
| **Kemudahan Setup** | ⭐⭐⭐⭐⭐ (Satu file `docker-compose.prod.yml`) | ⭐⭐⭐ (Perlu setup 3 service terpisah) |
| **Penyimpanan Data** | Terisolasi dalam Docker Volume terintegrasi | Dikembangkan terpisah di UI Dokploy |
| **Performa** | Sangat Cepat & Efisien | Sangat Cepat |

---

## 🚀 4. Langkah-Langkah Deployment (Opsi A: Docker Compose)

Ini adalah metode paling direkomendasikan karena repository ini **sudah dilengkapi** file [`docker-compose.prod.yml`](file:///Users/macbookair/Documents/Herd/fmikom-portal/docker-compose.prod.yml).

### Langkah 1: Buat Project Baru di Dokploy
1. Login ke Dashboard Dokploy VPS Anda (`https://dokploy.domain-vps-anda.com`).
2. Klik menu **Projects** di sidebar kiri ➔ Klik **Create Project**.
3. Beri nama project, contoh: `Portal-FMIKOM-Production`.

### Langkah 2: Tambahkan Service Compose
1. Di dalam Project `Portal-FMIKOM-Production`, klik tombol **Add Service** ➔ Pilih **Compose**.
2. Beri nama Compose Service: `fmikom-portal-app`.

### Langkah 3: Hubungkan Repository Git
1. Pada tab **General** / **Source Provider**:
   - Pilih **GitHub** (atau Custom Git).
   - Pilih Repository: `username/fmikom-portal` (atau nama repo Anda).
   - Branch: `main` atau `production`.
   - Compose File Path: `docker-compose.prod.yml`.

### Langkah 4: Isikan Environment Variables di Dokploy
1. Buka tab **Environment** pada Service Compose tersebut.
2. Buka file [`env.deployment`](file:///Users/macbookair/Documents/Herd/fmikom-portal/env.deployment) yang ada di repository ini.
3. Salin **seluruh isi** file [`env.deployment`](file:///Users/macbookair/Documents/Herd/fmikom-portal/env.deployment) ke kolom teks Environment Variables Dokploy.
4. **SESUAIKAN VARIABEL SENSITIF PROD**:
   - Ganti `APP_KEY` dengan kunci acak (Buat via terminal: `php artisan key:generate --show`).
   - Pastikan `APP_ENV=production` dan `APP_DEBUG=false`.
   - Set `APP_URL=https://fmikom.domainanda.com`.
   - Set `DB_PASSWORD` dan `DB_ROOT_PASSWORD` dengan password kuat.
   - Set `SESSION_SECURE_COOKIE=true` dan `SESSION_DOMAIN=fmikom.domainanda.com`.
   - Set `VITE_REVERB_HOST=fmikom.domainanda.com` dan `VITE_REVERB_PORT=443`.
   - Masukkan `GOOGLE_CLIENT_ID` & `GOOGLE_CLIENT_SECRET` asli.
5. Klik **Save Environment Variables**.

### Langkah 5: Pengaturan Domain & SSL Traefik di Dokploy
1. Buka tab **Domains** pada Service Compose di Dokploy.
2. Tambahkan domain untuk service `app`:
   - **Host**: `fmikom.domainanda.com`
   - **Path**: `/`
   - **Container Port**: `80`
   - **HTTPS / SSL**: Centang **Enable HTTPS / Certificate (Let's Encrypt)**.
3. Klik **Create Domain**.

### Langkah 6: Jalankan Deployment Pertamanya
1. Klik tombol **Deploy** di sudut kanan atas Dashboard Dokploy.
2. Buka tab **Logs** / **Deployments** untuk memantau proses kompilasi Multi-Stage Dockerfile.
3. Dokploy akan secara otomatis:
   - Menarik kode terbaru dari Git.
   - Menjalankan build image Composer & Node.js/Vite.
   - Membuat container `fmikom_app`, `fmikom_db`, dan `fmikom_redis`.
   - Menjalankan script [`docker/entrypoint.sh`](file:///Users/macbookair/Documents/Herd/fmikom-portal/docker/entrypoint.sh) untuk mengecek database, menyetel permission storage, membuat storage link, meng-cache route/config, dan menjalankan migrasi database otomatis.

---

## 🛠️ 5. Langkah-Langkah Deployment (Opsi B: Dokploy App + Managed Databases)

Jika Anda ingin mengelola MySQL dan Redis secara terpisah melalui UI Dokploy:

### Langkah 1: Buat Database Service MySQL & Redis di Dokploy
1. **Buat MySQL Database**:
   - Tambah Service ➔ Select **Database** ➔ **MySQL**.
   - Set Database Name: `fmikom_portal`, Username: `fmikom_user`, Password: `PasswordKuat123!`.
   - Catat internal host name MySQL yang diberikan Dokploy (misal `dokploy-mysql-xyz`).
2. **Buat Redis Service**:
   - Tambah Service ➔ Select **Database** ➔ **Redis**.
   - Catat internal host name Redis.

### Langkah 2: Buat Application Service (Dockerfile)
1. Tambah Service ➔ Select **Application**.
2. Connect ke GitHub Repository & Branch `main`.
3. Set **Build Type** ke **Dockerfile**.
4. Set Dockerfile Path: `Dockerfile`.
5. Di tab **Environment Variables**, isi konfigurasi dari [`env.deployment`](file:///Users/macbookair/Documents/Herd/fmikom-portal/env.deployment), ganti `DB_HOST` dan `REDIS_HOST` dengan nama container/hostname internal dari langkah 1.
6. Konfigurasi Domain pada Port `80` + HTTPS SSL.
7. Klik **Deploy**.

---

## 🔑 6. Panduan Rinci Variabel Lingkungan (`env.deployment`)

Berikut adalah penjelasan fungsi & aturan pengisian setiap variabel di file [`env.deployment`](file:///Users/macbookair/Documents/Herd/fmikom-portal/env.deployment):

### 🚨 1. Variabel Kritis (WAJIB DIUBAH DI PRODUCTION)

```env
# Must be 'production'
APP_ENV=production

# Application Key 32-character base64 string
APP_KEY=base64:XxxxxXxxxx...
```
> 💡 **Cara Membuat `APP_KEY`**:
> Jalankan perintah berikut di terminal komputer lokal Anda:
> ```bash
> php artisan key:generate --show
> ```
> Salin teks yang dihasilkan (dimulai dengan `base64:...`) dan tempelkan ke `APP_KEY`.

```env
# Matikan debug mode demi keamanan & pencegahan kebocoran credential
APP_DEBUG=false

# URL domain resmi yang diakses publik
APP_URL=https://fmikom.domainanda.com
```

```env
# Password Database & Root MySQL
DB_DATABASE=fmikom_portal
DB_USERNAME=fmikom_user
DB_PASSWORD=PasswordDatabaseSuperAman123!
DB_ROOT_PASSWORD=PasswordRootSuperAman456!
```

### 🔒 2. Keamanan Session & Cookie (HTTPS)

```env
SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_DOMAIN=fmikom.domainanda.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```
- `SESSION_SECURE_COOKIE=true`: Memastikan cookie session hanya dikirim melalui koneksi terenkripsi (HTTPS). **Wajib true di SSL/VPS**.
- `SESSION_ENCRYPT=true`: Mengamalkan payload cookie agar tidak dapat di-tamper.

### 📡 3. Realtime WebSockets (Laravel Reverb)

```env
REVERB_APP_ID=100800
REVERB_APP_KEY=fmikom-reverb-key-prod-99
REVERB_APP_SECRET=fmikom-reverb-secret-prod-8877

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=fmikom.domainanda.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```
- `VITE_REVERB_HOST`: Nama domain utama aplikasi Anda.
- `VITE_REVERB_PORT=443` & `VITE_REVERB_SCHEME=https`: Agar koneksi WebSocket dari browser frontend menggunakan wss:// (Secure WebSockets) melalui port HTTPS resmi Traefik 443.

---

## 💾 7. Manajemen Storage & Data Persistent Volume

Aplikasi Portal FMIKOM menyimpan berkas pengguna (seperti pasfoto, sertifikat, portofolio, dan media modul Pagi/WIMS/TRACE/FAST) di folder `/var/www/html/storage/app`.

Pada file [`docker-compose.prod.yml`](file:///Users/macbookair/Documents/Herd/fmikom-portal/docker-compose.prod.yml), volume telah dikonfigurasi sebagai berikut:

```yaml
volumes:
  - app_storage:/var/www/html/storage/app
  - db_data:/var/lib/mysql
  - redis_data:/data
```

> ⚠️ **PENTING**:
> JANGAN menghapus volume `app_storage`, `db_data`, atau `redis_data` di Dokploy saat melakukan update/re-deploy agar data user & database tidak hilang.

---

## ⚡ 8. Konfigurasi WebSocket & Reverse Proxy (Traefik/Nginx)

Sistem Supervisor di container runtime otomatis mengaktifkan server Laravel Reverb di port internal `8080` (diatur pada [`docker/supervisord.conf`](file:///Users/macbookair/Documents/Herd/fmikom-portal/docker/supervisord.conf)).

### Traefik WebSocket Support
Dokploy menggunakan **Traefik** sebagai reverse proxy utama. Traefik secara otomatis mendukung upgrade HTTP ke WebSockets (`wss://`). Pastikan:
1. `VITE_REVERB_HOST` di-set ke domain publik Anda (misal `fmikom.domainanda.com`).
2. `VITE_REVERB_PORT=443` dan `VITE_REVERB_SCHEME=https`.

---

## 🛠️ 9. Perintah Pemeliharaan (Maintenance & Exec Commands)

Untuk menjalankan perintah Artisan di VPS yang berjalan di Dokploy:

### A. Masuk ke Kontainer Aplikasi via Terminal
Di Dokploy Dashboard ➔ Buka Service `fmikom-portal-app` ➔ Tab **Terminal** / **Exec** (atau jalankan via SSH VPS):

```bash
# Dapatkan ID container fmikom_app
docker ps

# Masuk ke container app
docker exec -it fmikom_app sh
```

### B. Menjalankan Artisan Commands Manual
Inside the container:

```bash
# 1. Menjalankan Migrasi Database Manual
php artisan migrate --force

# 2. Menjalankan Database Seeder (Data Awal / Super Admin)
php artisan db:seed --force

# 3. Membersihkan dan Meng-cache Ulang Konfigurasi
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Mengecek Status Supervisor (Queue Worker, Reverb, Cron)
supervisorctl status
```

---

## 🔍 10. Troubleshooting Masalah Umum

### ❓ 1. Tampilan Blank atau Error `500 Internal Server Error`
- **Penyebab**: `APP_KEY` belum diisi, permission folder storage belum sesuai, atau koneksi database gagal.
- **Solusi**: 
  1. Cek log container di Dokploy pada tab **Logs**.
  2. Pastikan `APP_KEY` terisi di Environment Variables.
  3. Masuk terminal container dan jalankan `php artisan config:cache`.

### ❓ 2. CSS / JavaScript / Asset Vite Tidak Muncul atau Mixed Content Error
- **Penyebab**: `APP_URL` menggunakan `http://` padahal domain sudah `https://`.
- **Solusi**: Ubah `APP_URL` di Dokploy Environment menjadi `https://fmikom.domainanda.com` lalu re-deploy.

### ❓ 3. Database Connection Refused (`SQLSTATE[HY000] [2002]`)
- **Penyebab**: Host `DB_HOST=mysql` tidak dapat ditemukan atau container MySQL belum siap.
- **Solusi**: Script [`docker/entrypoint.sh`](file:///Users/macbookair/Documents/Herd/fmikom-portal/docker/entrypoint.sh) sudah dirancang untuk menunggu 30 detik sampai MySQL siap. Pastikan nama service MySQL di Dokploy / Compose persis sesuai dengan `DB_HOST`.

### ❓ 4. Realtime Notifications / WebSockets Gagal Terhubung
- **Penyebab**: Mismatch antara port WebSocket frontend (`VITE_REVERB_PORT`) dan SSL proxy.
- **Solusi**: Pastikan `VITE_REVERB_HOST=fmikom.domainanda.com`, `VITE_REVERB_PORT=443`, dan `VITE_REVERB_SCHEME=https`.

---

## ✅ Summary Check-List Sebelum Launching (Go-Live)
- [ ] `APP_ENV=production` & `APP_DEBUG=false`
- [ ] `APP_KEY` sudah terisi dengan kunci unik baru
- [ ] Domain sudah HTTPS dengan SSL aktif dari Dokploy Traefik
- [ ] Volume `app_storage` dan `db_data` sudah di-mounting
- [ ] `SESSION_SECURE_COOKIE=true` & `SESSION_ENCRYPT=true`
- [ ] Google OAuth Redirect URI terdaftar resmi di Google Cloud Console
- [ ] Email SMTP teruji dapat mengirim email notifikasi/OTP
