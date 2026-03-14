# 🏛️ FMIKOM Integrated Portal

[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](#) [![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](#) [![License](https://img.shields.io/badge/license-Internal-red.svg)](#)

## 📌 Tentang Proyek
**FMIKOM Integrated Portal** adalah platform *enterprise* tersentralisasi yang dirancang khusus untuk mendigitalisasi, mengelola, dan mengotomatisasi ekosistem operasional dan akademik. Proyek ini dikembangkan dengan arsitektur yang *scalable* dan aman untuk memastikan pengalaman pengguna yang *seamless*, baik bagi staf, administrator, maupun civitas tingkat lanjut. 

Portal ini menyatukan berbagai subsistem krusial ke dalam satu ekosistem berbasis web, meminimalkan redundansi data, dan memaksimalkan efisiensi operasional harian.

---

## 🚀 Fitur Utama

Sistem ini ditenagai oleh 4 pilar subsistem utama:

### 1. ⚡ FAST (Fast Academic & Service Tracker)
Modul layanan esensial yang berfokus pada kecepatan dan efisiensi. 
* **Fungsi:** Mengakselerasi birokrasi dan permohonan layanan administrasi secara *real-time*.
* **Keunggulan:** Alur persetujuan (approval workflow) otomatis, SLA *tracking*, dan notifikasi instan untuk memangkas waktu tunggu layanan.

### 2. 🌅 PAGI (Portal Anjungan & Gateway Informasi)
Dasbor sentral dan gerbang informasi utama bagi seluruh pengguna.
* **Fungsi:** Menyajikan ringkasan data harian, pengumuman penting, dan *quick access* ke menu-menu operasional saat pengguna pertama kali *login*.
* **Keunggulan:** Antarmuka intuitif yang dapat dikustomisasi (*personalized dashboard*) sesuai dengan *role* atau otorisasi masing-masing pengguna.

### 3. 🔍 TRACE (Tracking & Record Assessment System)
Sistem pelacakan data historis dan pergerakan dokumen/status.
* **Fungsi:** Memberikan visibilitas penuh terhadap rekam jejak (bisa berupa *tracer study* alumni, pelacakan dokumen/surat menyurat, atau progres proyek/tugas akhir).
* **Keunggulan:** Dilengkapi dengan fitur log audit sistem dan pencarian *advanced* berbasis filter presisi tinggi.

### 4. 📊 WIMS (Web Information Management System)
Sistem manajemen basis data dan informasi operasional *back-end*.
* **Fungsi:** Mengelola metadata, inventaris informasi, dan pemetaan beban kerja (*workload management*) di dalam lingkungan FMIKOM.
* **Keunggulan:** Integrasi API yang solid, pelaporan/laporan analitik (*analytics reporting*), dan manajemen *User Role & Permission* (RBAC).

---

## 🛠️ Arsitektur & Teknologi Terkait
* **Frontend:** Blade Templating / React.js / Vue.js
* **Backend:** Laravel (PHP)
* **Database:** MySQL / PostgreSQL
* **Infrastructure:** GitHub Actions (CI/CD)

---

## ⚙️ Panduan Memulai (Getting Started)

Untuk menjalankan proyek ini di lingkungan lokal (*development*), ikuti langkah-langkah berikut:

1. **Clone Repositori**
   ```bash
   git clone [https://github.com/fmikom-dev-team/portal-fmikom.git](https://github.com/fmikom-dev-team/portal-fmikom.git)
   
2. **Masuk ke Folder Proyek & Instalasi Dependensi**

    ```bash
    cd portal-fmikom
    composer install
    npm install
    
3. **Konfigurasi Environment**
    Salin file .env.example menjadi .env dan sesuaikan kredensial database Anda.

    ```bash
    cp .env.example .env
    php artisan key:generate
    
4. **Jalankan Migrasi Database & Aplikasi**

    ```bash
    php artisan migrate
    php artisan serve
