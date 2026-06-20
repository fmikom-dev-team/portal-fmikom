# Panduan Uji Kinerja dengan k6 (Fmikom Portal)

Folder ini berisi konfigurasi skenario uji kinerja menggunakan **k6** untuk menguji ketahanan, batas kapasitas, kestabilan, dan modul-modul spesifik pada sistem.

## Jenis Profil Pengujian Beban

1. **Load Test (`load.js`)**
   - **Tujuan**: Mengetahui kinerja sistem di bawah beban yang diperkirakan normal dalam penggunaan sehari-hari.
   - **Skenario**: Naik dari 0 ke 50 VUs (Virtual Users) dalam 1 menit, bertahan selama 3 menit, lalu turun kembali ke 0.

2. **Stress Test (`stress.js`)**
   - **Tujuan**: Menemukan titik jenuh (breaking point) dari sistem dan melihat perilakunya ketika batas kapasitas terlampaui.
   - **Skenario**: Beban bertahap dari 100, lalu naik ke 200, dan berakhir di 400 VUs untuk memicu overload.

3. **Spike Test (`spike.js`)**
   - **Tujuan**: Menguji ketahanan dan kecepatan pemulihan sistem jika terjadi lonjakan pengguna secara tiba-tiba (misal: saat pengumuman resmi atau pendaftaran baru dibuka).
   - **Skenario**: Lonjakan ekstrem langsung ke 500 VUs dalam waktu sangat singkat (30s - 1m) lalu langsung dinormalkan kembali.

4. **Endurance / Soak Test (`endurance.js`)**
   - **Tujuan**: Menguji keandalan sistem dalam jangka panjang untuk mendeteksi adanya kebocoran memori (memory leaks), masalah pengosongan memori (garbage collection), atau kebocoran koneksi database.
   - **Skenario**: Beban konstan 50 VUs dijalankan selama 30 menit (dapat ditingkatkan ke 2-8 jam untuk uji yang lebih mendalam).

5. **High Load 10k Test (`high_load_10k.js`)**
   - **Tujuan**: Simulasi beban sangat tinggi berskala 10.000 VUs untuk menguji daya tampung server produksi utama saat peak traffic ekstrem.
   - **Skenario**: Ramp-up bertahap hingga 10.000 VUs, menahan beban selama 5 menit, kemudian recovery/ramp-down kembali ke 0.

---

## Modul dan Fitur yang Dapat Diuji (`tests/k6/*.js`)

Kami telah menyediakan skrip uji khusus untuk modul/fitur spesifik sesuai kebutuhan Anda:

| File Skenario | Target Fitur / Deskripsi | Persyaratan Kredensial |
| :--- | :--- | :--- |
| **`login.js`** | Menguji kecepatan otentikasi user dan middleware pelindung (`radar.shield`). | Default user (`mahasiswa@example.com`) |
| **`dashboard.js`** | Menguji halaman dashboard utama setelah pengguna berhasil login. | Default user (`mahasiswa@example.com`) |
| **`mahasiswa.js`** | Menguji halaman kelola data mahasiswa dan API autocomplete pencarian mahasiswa. | Default admin (`muchlisinmaruf@gmail.com`) |
| **`dosen.js`** | Menguji API pemfilteran kontak dengan tipe user `dosen`. | Default user (`mahasiswa@example.com`) |
| **`berita.js`** | Menguji loading halaman index berita & membaca artikel berita secara dinamis (tanpa login). | Publik (Tidak butuh login) |
| **`map.js`** | Menguji endpoint penyimpanan koordinat lokasi peta (latitude & longitude) mahasiswa. | Default user (`mahasiswa@example.com`) |
| **`notification.js`** | Menguji pengambilan notifikasi user dan aksi menandai semua dibaca (`mark-all-read`). | Default user (`mahasiswa@example.com`) |
| **`upload.js`** | Menguji performa upload file/gambar menggunakan payload multi-part form data. | Default admin (`muchlisinmaruf@gmail.com`) |
| **`crud.js`** | Menguji performa operasi basis data (Create, Read, Update, Delete) pada data kategori. | Default admin (`muchlisinmaruf@gmail.com`) |
| **`api.js`** | Menguji endpoint API internal berupa data statistik dan grafik admin dashboard. | Default admin (`muchlisinmaruf@gmail.com`) |

---

## Cara Menjalankan Uji Kinerja

Secara default, skrip diarahkan untuk menguji URL produksi (`https://fmikom.suntree.my.id`). Anda bisa mengubah URL target serta kredensial secara dinamis melalui Environment Variable.

### 1. Menjalankan Uji pada URL Default (https://fmikom.suntree.my.id)
```bash
# Menjalankan uji otentikasi login
k6 run tests/k6/login.js

# Menjalankan uji CRUD data kategori
k6 run tests/k6/crud.js

# Menjalankan uji upload gambar/file
k6 run tests/k6/upload.js
```

### 2. Menjalankan Uji pada Local Development (misal: localhost:8000 atau Octane)
Gunakan opsi `--env TARGET_URL=...` untuk mengganti target secara langsung tanpa mengedit file:
```bash
# Contoh running login test ke local Octane server port 8009
k6 run --env TARGET_URL=http://127.0.0.1:8009 tests/k6/login.js

# Mengubah user test default menggunakan env variable
k6 run --env TARGET_URL=http://127.0.0.1:8009 --env TEST_USER_EMAIL=admin@example.com --env TEST_USER_PASS=securepassword tests/k6/dashboard.js
```

### 3. Menyimpan Laporan / Output Hasil Test
Untuk menyimpan hasil test atau membuat visualisasi performa, Anda bisa mengekspor hasilnya ke format JSON:
```bash
# Simpan hasil dalam bentuk file JSON summary
k6 run --summary-export=load-report.json tests/k6/load.js
```
