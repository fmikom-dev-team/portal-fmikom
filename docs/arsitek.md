# DOKUMEN ARSITEKTUR & TEKNOLOGI SISTEM
## MODUL PAGI — PORTOFOLIO & GALERI DIGITAL
### FMIKOM Portal — Fakultas Ilmu Komputer

---

> **Tanggal Dokumen**: 19 Agustus 2026
> **Versi Sistem**: Laravel 12 + Vue 3 + Inertia.js
> **Lingkup**: Modul Pagi — Backend, Frontend, Database, Infrastruktur, dan Keamanan
> **Sifat Dokumen**: Laporan Internal Teknis (Tidak untuk dipublikasikan)

---

## DAFTAR ISI

1. Konsep Arsitektur Sistem
2. Diagram Arsitektur
3. Alur Komunikasi Antarkomponen
4. Framework Backend
5. Framework Frontend
6. Database
7. Library & API Pendukung
8. Infrastruktur Pengembangan
9. Infrastruktur Deployment
10. Keamanan dan Autentikasi

---

## 1. KONSEP ARSITEKTUR SISTEM

### 1.1 Pola Arsitektur Utama

Modul PAGI dibangun di atas pola **Modular Monolith** — arsitektur monolitik yang dibagi secara internal ke dalam modul-modul terpisah dengan batasan tanggung jawab (*bounded context*) yang jelas. Modul PAGI adalah satu dari delapan modul yang ada di sistem FMIKOM Portal:

```
app/Modules/
├── Coreportal/   <- Modul inti portal
├── Fast/         <- Modul lain
├── Pagi/         <- MODUL INI (Portofolio & Galeri Digital)
├── Portal/       <- Admin portal
├── Settings/     <- Pengaturan sistem
├── Trace/        <- Modul pelacakan
├── Wims/         <- Modul manajemen wawasan
└── WorkOs/       <- Sistem manajemen kerja
```

### 1.2 Pola Arsitektur Frontend-Backend

| Aspek | Implementasi |
|---|---|
| **Paradigma Arsitektur** | Modular Monolith (MVC + Service Layer) |
| **Komunikasi Frontend-Backend** | Inertia.js Protocol (Hybrid SPA/MPA) |
| **Pola Desain Backend** | MVC + Action Pattern + Service Layer + Concern Trait |
| **Pola Desain Frontend** | Composition API (Vue 3) + Composables |
| **State Management** | Inertia.js Shared Data + Vue Composables (tanpa Vuex/Pinia) |

### 1.3 Prinsip Desain Kunci

1. **Block-Based Content Modeling** — Konten portofolio disimpan sebagai array JSON terstruktur di kolom `content`. Setiap elemen disebut "block" (teks, gambar, video, grid foto, dll.), memungkinkan rendering yang fleksibel dan aman dari serangan XSS.

2. **Deferred Payload & Lazy Loading** — Galeri karya utama menggunakan *Inertia Deferred Prop*. Data berat seperti konten blok lengkap dan komentar karya **tidak** dimuat saat halaman dibuka, melainkan di-*lazy-load* via endpoint terpisah ketika pengguna membuka modal pratinjau karya.

3. **Automated Cache Invalidation** — Setiap operasi CRUD pada `PagiWork` (create, update, delete) secara otomatis memicu penghapusan cache Redis publik (`pagi_feed_projects_raw`, `pagi_admin_stats`) melalui Eloquent Model Booted Events. Ini memastikan galeri publik selalu menampilkan data terbaru.

4. **Action Pattern** — Operasi interaksi sosial (like, comment, follow, dll.) dipisahkan ke dalam kelas `Actions/` tersendiri, memastikan setiap operasi hanya memiliki satu tanggung jawab.

5. **Service Layer Pattern** — Logika bisnis kompleks (moderasi konten, manajemen galeri, CV, chat) dipisahkan dari Controller ke dalam kelas `Services/`. Controller berfungsi sebagai *thin controller* yang mendelegasikan ke Service.

---

## 2. DIAGRAM ARSITEKTUR

### 2.1 Diagram Arsitektur Sistem Tingkat Tinggi

```
+--------------------------------------------------------------------+
|                         USER BROWSER                               |
|                                                                    |
|  Vue 3 (Composition API + TypeScript) + Tailwind CSS v4           |
|  Inertia.js Router (SPA Navigation)                               |
|  Tiptap v3 Rich-Text Block Editor                                 |
|  Editor.js (Block Editor alternatif)                              |
|  Laravel Echo + Pusher.js (WebSocket Client)                      |
|  Service Worker PWA (sw-pwa.js)                                   |
+-------------------------------+------------------------------------+
                                |
                HTTPS/JSON Inertia Protocol
                WSS:// WebSocket (Laravel Reverb)
                                |
+-------------------------------v------------------------------------+
|                     LARAVEL BACKEND (PHP 8.4)                      |
|                                                                    |
|  Middleware: auth > EnsureFirstTimeLogin > module.context:pagi    |
|    > throttle:pagi-api > throttle:uploads                         |
|                                                                    |
|  Controllers (app/Modules/Pagi/Controllers/):                     |
|    PagiDashboardController   <- Dashboard, galeri, profil, notif  |
|    PagiEditorController      <- Editor portofolio & karya         |
|    PagiChatController        <- Pesan langsung (direct message)   |
|    PagiCvController          <- CV Builder                        |
|    AdminDashboardController  <- Dashboard admin & statistik       |
|    AdminModerationController <- Moderasi laporan & konten         |
|    AdminShowcaseController   <- Manajemen showcase/pameran        |
|    AdminUserController       <- Manajemen pengguna & peringatan   |
|    AdminWorkController       <- Manajemen karya dari sisi admin   |
|                                                                    |
|  Services (app/Modules/Pagi/Services/):                           |
|    PortfolioService          <- CRUD portofolio, upload media     |
|    PagiSocialService         <- Galeri eksplorasi, follow         |
|    PagiChatService           <- Logika chat real-time             |
|    PagiNotificationService   <- Manajemen notifikasi              |
|    PagiProfileService        <- Profil publik, data pengguna      |
|    PagiCertificateService    <- CRUD sertifikat & media           |
|    PagiEducationService      <- Riwayat pendidikan & sinkronasi   |
|    PagiCvService             <- CV Builder (template, PDF)        |
|    ContentModerationService  <- Moderasi teks + gambar (AI)       |
|                                                                    |
|  Actions: LikeWork, LikeComment, LikeReply, CreateComment,       |
|           ReplyComment, FollowUser                                |
|                                                                    |
|  Concerns: FormatsPortfolioData (Trait pemformatan data)          |
|                                                                    |
+-------------------------------+------------------------------------+
                                |
                                v
+--------------------------------------------------------------------+
|               PERSISTENCE & INFRASTRUCTURE LAYER                   |
|                                                                    |
|  +---------------+  +--------------+  +----------------------+    |
|  | MySQL 8.0/8.4 |  | Redis Alpine |  |  Laravel Storage     |    |
|  |               |  |              |  |                      |    |
|  | pagi_works    |  | Cache:       |  | pagi/covers/         |    |
|  | pagi_work_    |  |  Feed Galeri |  | pagi/gallery/        |    |
|  |   likes       |  |  Stats Admin |  | pagi/certificates/   |    |
|  | pagi_work_    |  |  People Data |  | pagi/cv-photos/      |    |
|  |   comments    |  | Session Auth |  | pagi/org-logos/      |    |
|  | pagi_comment_ |  | Queue Worker |  |                      |    |
|  |   likes       |  | Rate Limiter |  |                      |    |
|  | pagi_messages |  +--------------+  +----------------------+    |
|  | pagi_reports  |                                                |
|  | pagi_warnings |  +------------------------------------------+  |
|  | pagi_follows  |  | Meilisearch (Full-Text Search)           |  |
|  | pagi_blocks   |  | Laravel Scout + PagiWork::search()       |  |
|  | pagi_cvs      |  +------------------------------------------+  |
|  | pagi_tags     |                                                |
|  +---------------+  +------------------------------------------+  |
|                     | Laravel Reverb (WebSocket Server)        |  |
|                     | Port 8080 internal, wss:// produksi      |  |
|                     | Events: PagiMessageSent, Reacted,        |  |
|                     |         Deleted, Edited, UnreadCount     |  |
|                     +------------------------------------------+  |
+--------------------------------------------------------------------+
```

### 2.2 Diagram Alur Publikasi Karya

```
Mahasiswa -> [Tiptap Block Editor]
          -> Klik "Publish Karya"
          -> POST /pagi/editor (Title, JSON Content, Cover, Category, Tags)
          -> [PagiEditorController::store()]
               |
               +--> Validasi Input (StorePortfolioRequest)
               +--> PortfolioService::saveCoverImage() -> Compress + Storage
               +--> PortfolioService::processContentBlocks() -> Sanitasi XSS
               +--> ContentModerationService::scan() -> Cek teks + gambar
               +--> PagiWork::create() -> INSERT ke pagi_works
               +--> [Model::booted()] -> Cache::forget('pagi_feed_projects_raw')
               +--> Redirect ke galeri dengan flash success
          <- Karya baru tampil di galeri
```

### 2.3 Diagram Alur Moderasi Konten

```
User -> POST /pagi/works/report
     -> AdminModerationController::storeReport()
          -> INSERT pagi_reports (status: pending)
          -> Notifikasi ke Admin

Admin -> GET /pagi/admin/reports -> Tampil daftar laporan

Admin -> POST /pagi/admin/content/work/{id}/moderate
      -> AdminModerationController::hideContent()
          -> UPDATE pagi_works SET status='hidden'
          -> Cache::forget('pagi_feed_projects_raw')
          -> Kirim notifikasi ke pemilik karya

Admin -> POST /pagi/admin/users/{user}/warn
      -> INSERT pagi_warnings
      -> Kirim peringatan digital ke mahasiswa
```

### 2.4 Diagram Alur Chat Real-Time

```
User A -> POST /pagi/messages
       -> PagiChatService::sendMessage()
            -> INSERT ke pagi_messages
            -> broadcast(new PagiMessageSent($message))
            -> Laravel Reverb WebSocket Server
            -> Echo::channel('pagi-messages.{user_id}')
       -> User B [JS Event Listener]
            -> Update UI real-time tanpa refresh halaman
```

---

## 3. ALUR KOMUNIKASI ANTARKOMPONEN

### 3.1 Inertia.js Protocol (Request-Response Utama)

Seluruh navigasi halaman dan pengiriman form di Modul PAGI menggunakan **Inertia.js Protocol** — bukan REST API konvensional maupun SSR murni.

**Cara Kerja:**

1. **Navigasi Awal (Full Page Load)**: Browser memuat halaman lengkap dari server Laravel. Server merender layout HTML dan mengirimkan data komponen Vue sebagai props JSON awal.
2. **Navigasi Selanjutnya (XHR Inertia)**: Klik tautan atau submit form dilakukan via XMLHttpRequest dengan header `X-Inertia: true`. Server merespons dengan **JSON Inertia** (bukan HTML penuh) berisi nama komponen Vue dan data props baru.
3. **Rendering Client-Side**: Vue 3 menerima respons Inertia, memperbarui komponen yang relevan tanpa me-reload seluruh halaman.

```
Browser         Inertia.js       Laravel Router      Controller
   |                |                  |                  |
   |-- Klik Link -> |                  |                  |
   |                |-- XHR+X-Inertia->|                  |
   |                |                  |-- Route Match -> |
   |                |                  |          Service/DB Query
   |                |         <- Inertia::render('Page', $props)
   |                |<- JSON Response--|                  |
   |<- Update Vue --|                  |                  |
```

### 3.2 Lazy Loading (AJAX/Fetch)

Untuk data berat yang tidak dimuat saat halaman awal dibuka:

| Parameter | Detail |
|---|---|
| **Endpoint** | `GET /pagi/preview/{preview}/data` |
| **Trigger** | Pengguna membuka modal pratinjau karya |
| **Data yang dimuat** | Konten blok lengkap, komentar + balasan, data kolaborator |
| **Implementasi** | `fetch()` dari Vue composable ke endpoint throttle (60 req/menit) |

### 3.3 WebSocket — Real-Time Events

**Laravel Echo + Laravel Reverb** untuk komunikasi dua arah real-time:

| Event | Channel | Keterangan |
|---|---|---|
| `PagiMessageSent` | `pagi-messages.{user_id}` | Pesan baru diterima |
| `PagiMessagesRead` | `pagi-messages.{user_id}` | Pesan telah dibaca |
| `PagiMessageDeleted` | `pagi-messages.{user_id}` | Pesan dihapus |
| `PagiMessageEdited` | `pagi-messages.{user_id}` | Pesan diedit |
| `PagiMessageReacted` | `pagi-messages.{user_id}` | Reaksi emoji ditambahkan |
| `PagiUnreadCountUpdated` | `pagi-messages.{user_id}` | Jumlah unread diperbarui |

### 3.4 API Polling — Admin Dashboard

Dashboard admin menggunakan HTTP polling berkala ke endpoint JSON internal:

| Endpoint | Data |
|---|---|
| `GET /pagi/admin/api/stats` | Statistik real-time |
| `GET /pagi/admin/api/chart` | Data grafik |
| `GET /pagi/admin/api/notifications` | Notifikasi admin |
| `GET /pagi/admin/api/analytics-stats` | Statistik analitik |
| `GET /pagi/admin/api/analytics-charts` | Data grafik analitik |

---

## 4. FRAMEWORK BACKEND

### 4.1 Laravel 12

| Parameter | Detail |
|---|---|
| **Versi Framework** | Laravel `^12.0` |
| **Versi PHP (Minimum)** | PHP `^8.2` |
| **Runtime PHP (Produksi)** | `PHP 8.4-fpm-alpine` |
| **Namespace Modul** | `App\Modules\Pagi\*` |
| **Total Rute Modul PAGI** | 70+ rute di `routes/pagi.php` |

**Komponen Laravel yang Digunakan di Modul PAGI:**

| Komponen | Penggunaan di PAGI |
|---|---|
| `Inertia::render()` | Rendering halaman Vue dari setiap Controller |
| `Illuminate\Foundation\Http\FormRequest` | 11 Form Request untuk validasi input |
| `Illuminate\Support\Facades\Cache` | Caching feed galeri & statistik admin (Redis) |
| `Illuminate\Support\Facades\Storage` | Penyimpanan file media karya & sertifikat |
| `Illuminate\Support\Facades\Http` | Integrasi Google Gemini AI REST API |
| `Illuminate\Support\Facades\Auth` | Cek identitas pengguna aktif |
| `Illuminate\Support\Facades\DB` | Query SQL mentah untuk optimasi N+1 |
| `Illuminate\Support\Facades\Log` | Pencatatan error & peringatan sistem |
| `Illuminate\Notifications\Notification` | Sistem notifikasi berbasis database |
| `Illuminate\Broadcasting\Event` | Broadcast event real-time via Reverb |

### 4.2 Struktur Controller Modul PAGI

**Rute User (Diakses Mahasiswa):**

| Controller | Ukuran | Fungsi Utama |
|---|---|---|
| `PagiDashboardController` | 50.7 KB | Dashboard feed, galeri, profil, notifikasi, interaksi sosial (like, comment, follow) |
| `PagiEditorController` | 32 KB | Manajemen portofolio: store, update, destroy, kolaborasi |
| `PagiChatController` | 9.7 KB | Sistem direct message, pin, archive, block |
| `PagiCvController` | 9 KB | CV Builder: store, edit, duplikasi, ekspor PDF |

**Rute Admin (Diakses Pengelola Fakultas):**

| Controller | Ukuran | Fungsi Utama |
|---|---|---|
| `AdminDashboardController` | 31.4 KB | Dashboard statistik & analitik real-time |
| `AdminModerationController` | 44.4 KB | Moderasi & kurasi konten: reports, warnings, takedowns, AI |
| `AdminUserController` | 21.5 KB | Manajemen pengguna: warn, revoke, status, notifikasi |
| `AdminShowcaseController` | 7 KB | Manajemen pameran showcase karya |
| `AdminWorkController` | 6.5 KB | Daftar semua karya dari sisi admin |

### 4.3 Lapisan Service

| Service | Ukuran | Tanggung Jawab |
|---|---|---|
| `PortfolioService` | 13.9 KB | Simpan cover image, proses content blocks JSON, sanitasi XSS, notifikasi kolaborator |
| `PagiSocialService` | 23.4 KB | Data galeri eksplorasi, pencarian pengguna, follow relations, galeri card builder |
| `PagiChatService` | 31.8 KB | Logika percakapan, unread count, riwayat chat, block/pin/archive |
| `PagiNotificationService` | 7.9 KB | Ambil, format, dan filter notifikasi berdasarkan role pengguna |
| `PagiProfileService` | 16.7 KB | Data profil publik, daftar karya per pengguna, resolusi asset |
| `PagiCertificateService` | 23.1 KB | CRUD sertifikat, upload media, kompresi gambar, sinkronasi ke CV |
| `PagiEducationService` | 4.7 KB | CRUD riwayat pendidikan, sinkronasi otomatis ke semua CV aktif |
| `PagiCvService` | 9.7 KB | Manajemen CV (template, simpan, duplikasi, ekspor PDF) |
| `ContentModerationService` | 24.2 KB | Moderasi teks (kamus lokal + Gemini AI), moderasi gambar (GD + Gemini Vision) |

### 4.4 Middleware Stack

| Middleware | Deskripsi |
|---|---|
| `auth` | Memastikan pengguna sudah login via Laravel Auth Session |
| `EnsureFirstTimeLoginComplete` | Memastikan pengguna telah melengkapi profil saat pertama login |
| `module.context:pagi` | Menetapkan konteks modul aktif ke session, validasi akses modul |
| `module.context:pagi,super-admin,admin,...` | Validasi akses admin dengan pembatasan role tertentu |
| `throttle:pagi-api` | Rate limiting untuk semua endpoint modul PAGI |
| `throttle:uploads` | Rate limiting khusus untuk endpoint upload file media |
| `throttle:pagi-chat-send` | Rate limiting khusus untuk pengiriman pesan chat |
| `throttle:30,1` | Rate limit 30 req/menit (like/view — BUG-FE-001 mitigation) |
| `throttle:20,1` | Rate limit 20 req/menit (komentar/balasan) |

---

## 5. FRAMEWORK FRONTEND

### 5.1 Vue 3 (Composition API)

| Parameter | Detail |
|---|---|
| **Versi** | Vue `^3.5.13` |
| **Syntax** | Script Setup (`<script setup lang="ts">`) dengan TypeScript |
| **API Style** | Composition API (tidak menggunakan Options API) |
| **Reaktivitas** | `ref()`, `computed()`, `watch()`, `reactive()` |

**Struktur Halaman Vue Modul PAGI:**

```
resources/js/pages/Modules/Pagi/
|-- Admin/
|   |-- Dashboard.vue              (24.3 KB)
|   |-- Analytics/
|   |-- Reports/
|   |-- Showcase/
|   |-- Tags/
|   |-- TextDictionary/
|   |-- ImageDictionary/
|   |-- Settings/
|   |-- Users/
|   +-- Works/
|
+-- User/
    |-- MahasiswaDashboard.vue     (45 KB)   <- Feed utama
    |-- Gallery.vue                (28.3 KB) <- Galeri eksplorasi
    |-- Messages.vue               (51.4 KB) <- Chat real-time
    |-- People.vue                 (26.3 KB) <- Komunitas
    |-- Notifications.vue          (15.5 KB)
    |-- Editor/
    |   |-- Editor.vue             (23.8 KB)
    |   |-- EditorCanvas.vue       (25.1 KB)
    |   |-- EditorSidebar.vue      (9.8 KB)
    |   |-- EditorPublishModal.vue (22.1 KB)
    |   |-- PagiTiptapEditor.vue   (10.6 KB)
    |   |-- useEditorCanvas.ts
    |   |-- useEditorDraft.ts
    |   |-- useEditorFileUpload.ts (16.2 KB)
    |   |-- useEditorTags.ts
    |   +-- useEditorCollaborators.ts
    |-- Profile/
    |-- Cv/
    |-- Chat/
    |-- Settings/
    |-- composables/
    +-- ui/
```

### 5.2 Inertia.js v2

| Parameter | Detail |
|---|---|
| **Versi** | `@inertiajs/vue3 ^2.3.27` |
| **Fungsi** | Bridge antara Laravel (server) dan Vue 3 (client) |
| **router.visit()** | Navigasi SPA tanpa reload |
| **router.post/put/delete()** | Submit form ke controller |
| **usePage()** | Akses shared props dari server (user, auth, flash) |
| **Deferred Props** | Lazy loading data berat (galeri modal, chat) |

### 5.3 Tailwind CSS v4

| Parameter | Detail |
|---|---|
| **Versi** | Tailwind CSS `^4.1.1` |
| **Integrasi** | `@tailwindcss/vite` (langsung di Vite, tanpa postcss config) |
| **Plugin Typography** | `@tailwindcss/typography ^0.5.19` |
| **Plugin Animasi** | `tw-animate-css ^1.2.5` |

### 5.4 Tiptap Editor v3 (Rich-Text Block Editor)

| Parameter | Detail |
|---|---|
| **Versi** | `@tiptap/vue-3 ^3.28.0` |
| **Total Ekstensi** | 25+ ekstensi resmi + 1 custom |

| Kategori | Ekstensi |
|---|---|
| **Dasar** | StarterKit, Placeholder, Focus |
| **Format Teks** | Bold, Italic, Underline, Strike, Highlight, Color, TextStyle, FontFamily, TextAlign, Typography |
| **Heading & Struktur** | Heading (H1-H6), Blockquote, HardBreak |
| **List** | BulletList, OrderedList, TaskList, TaskItem |
| **Media** | Image, Youtube, tiptap-extension-resize-image |
| **Kode** | CodeBlockLowlight (syntax highlighting), InlineCode |
| **Tabel** | Table, TableRow, TableCell, TableHeader |
| **Lanjutan** | CharacterCount, Link, Subscript, Superscript, GlobalDragHandle, BubbleMenu, FloatingMenu |
| **Custom** | `editorSuggestions.ts` (mention/autocomplete kolaborator) |

### 5.5 Editor.js (Block Editor Alternatif)

| Parameter | Detail |
|---|---|
| **Versi** | `@editorjs/editorjs ^2.31.6` |
| **Plugins** | Header, Image, List, Code, Table, Quote, Embed, Marker, Link, Attaches, Delimiter, Raw, InlineCode, Underline, NestedList, Strikethrough, Checklist, LinkAutocomplete |
| **Tambahan** | `editorjs-drag-drop ^1.1.16`, `editorjs-undo ^2.0.28` |

### 5.6 Teknologi Frontend Lainnya

| Library | Versi | Kegunaan di PAGI |
|---|---|---|
| `lucide-vue-next` | `^0.468.0` | Icon set UI |
| `reka-ui` | `^2.10.0` | Komponen UI primitif (Radix Vue fork) |
| `@vueuse/core` | `^12.8.2` | Composable utility (useStorage, useIntersectionObserver, dll.) |
| `apexcharts` + `vue3-apexcharts` | `^5.15.2` | Grafik statistik admin |
| `chart.js` + `vue-chartjs` | `^4.5.1` | Grafik tambahan |
| `dayjs` | `^1.11.20` | Manipulasi & format tanggal/waktu |
| `dompurify` | `^3.4.5` | Sanitasi HTML dari XSS di sisi browser |
| `framer-motion` + `motion` | `^12.40.0` | Animasi UI komponen |
| `laravel-echo` | `^2.3.4` | WebSocket client (listener event broadcast) |
| `pusher-js` | `^8.5.0` | Transport layer Laravel Echo |
| `axios` | `^1.18.1` | HTTP client untuk API calls tambahan |
| `cropperjs` | `^1.6.2` | Crop gambar profil/avatar |
| `vue-sonner` | `^2.0.9` | Toast notification |
| `tippy.js` | `^6.3.7` | Tooltip popup |
| `embla-carousel-vue` | `^8.6.0` | Carousel/slider komponen |
| `@ffmpeg/ffmpeg` + `@ffmpeg/core` | `^0.12.15` | Konversi video di browser (WebAssembly FFmpeg) |
| `html2canvas-pro` | `^2.3.1` | Screenshot halaman untuk ekspor CV |
| `jspdf` | `^4.2.1` | Ekspor dokumen CV ke format PDF |
| `qrcode` | `^1.5.4` | Generasi QR Code untuk CV/profil |
| `justified-layout` | `^4.1.0` | Tata letak galeri foto masonry |
| `leaflet` + plugins | `^1.9.4` | Peta interaktif (analytics admin) |
| `vuedraggable` | `^4.1.0` | Drag-and-drop pengurutan karya di profil |
| `vue-input-otp` | `^0.3.2` | Input OTP (untuk verifikasi 2FA) |

---

## 6. DATABASE

### 6.1 Sistem Manajemen Database

| Parameter | Nilai |
|---|---|
| **DBMS** | MySQL `8.0` (produksi) / MySQL `8.4` (pengembangan via Sail) |
| **Driver Laravel** | `DB_CONNECTION=mysql` |
| **ORM** | Laravel Eloquent ORM |
| **Migration System** | Laravel Migrations (per-modul di `database/migrations/pagi/`) |
| **Total File Migrasi PAGI** | 22 file migrasi |

### 6.2 Skema Tabel Database Modul PAGI

Seluruh tabel modul PAGI menggunakan prefix `pagi_` untuk menghindari konflik dengan modul lain.

#### 6.2.1 Tabel Konten Portofolio (Inti)

**`pagi_works`** — Tabel utama karya portofolio mahasiswa

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key auto-increment |
| `user_id` | BIGINT FK -> `users.id` | Pemilik karya |
| `title` | VARCHAR(255) | Judul karya |
| `content` | JSON/LONGTEXT | Konten dalam format JSON array of blocks |
| `cover_image` | VARCHAR(255), nullable | Path file gambar/video sampul |
| `is_published` | BOOLEAN | Status publikasi (true/false) |
| `visibility` | ENUM | Visibilitas: null / Everyone / Draft |
| `status` | ENUM | Status: active, warning, hidden, removed, review |
| `views_count` | BIGINT | Penghitung tayangan |
| `description` | TEXT, nullable | Deskripsi singkat |
| `category` | VARCHAR(255), nullable | Kategori ilmu |
| `tools_used` | TEXT/JSON, nullable | Daftar tool yang digunakan |
| `created_at`, `updated_at` | TIMESTAMP | Timestamps |

**`pagi_tags`** — Tabel tag/label karya

**`pagi_work_tags`** — Tabel pivot: relasi many-to-many antara `pagi_works` dan `pagi_tags`

#### 6.2.2 Tabel Interaksi Sosial (Normalized)

**`pagi_work_likes`** — Like/suka pada karya

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `work_id` | BIGINT FK -> `pagi_works.id` CASCADE | Karya yang disukai |
| `user_id` | BIGINT FK -> `users.id` CASCADE | Pengguna yang menyukai |
| `created_at`, `updated_at` | TIMESTAMP | Timestamps |
| UNIQUE | `(work_id, user_id)` | Satu pengguna hanya bisa like sekali |

**`pagi_work_comments`** — Komentar pada karya (mendukung nested reply)

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK | Primary key |
| `uuid` | VARCHAR(36), UNIQUE | UUID publik komentar |
| `work_id` | BIGINT FK -> `pagi_works.id` CASCADE | Karya yang dikomentari |
| `user_id` | BIGINT FK -> `users.id` CASCADE | Penulis komentar |
| `parent_id` | BIGINT FK -> `pagi_work_comments.id`, nullable | ID komentar induk (untuk reply) |
| `body` | TEXT | Isi komentar |
| `created_at`, `updated_at` | TIMESTAMP | Timestamps |

**`pagi_comment_likes`** — Like pada komentar

| Kolom | Tipe | Keterangan |
|---|---|---|
| `comment_id` | BIGINT FK -> `pagi_work_comments.id` CASCADE | Komentar yang disukai |
| `user_id` | BIGINT FK -> `users.id` CASCADE | Pengguna yang menyukai |
| UNIQUE | `(comment_id, user_id)` | Pencegahan duplikasi like |

#### 6.2.3 Tabel Moderasi

**`pagi_reports`** — Laporan pelanggaran karya

| Kolom | Keterangan |
|---|---|
| `work_id` | FK ke `pagi_works` |
| `reporter_id` | FK ke `users` (pelapor) |
| `reason` | Alasan laporan |
| `status` | Status: pending, reviewed, archived |
| `admin_note` | Catatan admin |

**`pagi_warnings`** — Teguran/peringatan digital ke mahasiswa

| Kolom | Keterangan |
|---|---|
| `user_id` | FK ke `users` |
| `work_id` | FK ke `pagi_works` (nullable) |
| `reason` | Alasan peringatan |
| `issued_by` | FK ke `users` (admin yang memberi peringatan) |
| `revoked_at` | Waktu pencabutan peringatan (nullable) |

#### 6.2.4 Tabel Sosial & Relasi

**`pagi_follows`** — Relasi follow antar pengguna

| Kolom | Keterangan |
|---|---|
| `follower_id` | FK ke `users` (yang mengikuti) |
| `following_id` | FK ke `users` (yang diikuti) |
| UNIQUE | `(follower_id, following_id)` |

**`pagi_blocks`** — Relasi pemblokiran antar pengguna

| Kolom | Keterangan |
|---|---|
| `user_id` | FK ke `users` (pemblokir) |
| `blocked_id` | FK ke `users` (yang diblokir) |
| UNIQUE | `(user_id, blocked_id)` |

#### 6.2.5 Tabel Chat & Pesan

**`pagi_messages`** — Pesan langsung (Direct Message)

| Kolom | Keterangan |
|---|---|
| `sender_id` | FK ke `users` |
| `receiver_id` | FK ke `users` |
| `conversation_id` | String identifier percakapan unik |
| `content` | Isi pesan (terenkripsi end-to-end di client) |
| `read_at` | Timestamp ketika dibaca |
| `deleted_at` | Soft delete timestamp |
| `is_edited` | Boolean penanda pesan diedit |
| `reactions` | JSON (emoji reactions) |

**Tabel Status Percakapan Per Pengguna:**

| Tabel | Fungsi |
|---|---|
| `pagi_messages_archive` | Arsip pesan lama |
| `pagi_active_chats` | Daftar percakapan aktif |
| `pagi_pinned_chats` | Percakapan yang di-pin |
| `pagi_archived_chats` | Percakapan yang diarsipkan |
| `pagi_unread_chats` | Percakapan belum dibaca |
| `pagi_cleared_chats` | Riwayat penghapusan chat (timestamp cleared_at) |

#### 6.2.6 Tabel CV Builder

**`pagi_cvs`** — Dokumen CV digital mahasiswa

| Kolom | Keterangan |
|---|---|
| `user_id` | FK ke `users` |
| `name` | Nama/label dokumen CV |
| `template` | Nama template yang dipilih |
| `content` | JSON seluruh konten CV |
| `education` | JSON riwayat pendidikan (sinkronasi dari `users.metadata`) |
| `photo_path` | Path foto profil di CV |

#### 6.2.7 Indeks Performa Database

```sql
-- Feed Per User
INDEX(user_id, is_published, status) ON pagi_works

-- Galeri Publik
INDEX(is_published, status, visibility) ON pagi_works

-- Sorting Most Viewed
INDEX(views_count) ON pagi_works

-- Count Likes Per Karya
INDEX(work_id) ON pagi_work_likes

-- Comment Threading (Nested Reply)
INDEX(work_id, parent_id) ON pagi_work_comments

-- Chat History Lookup
INDEX(sender_id, receiver_id) ON pagi_messages

-- Chat Timeline
INDEX(conversation_id, created_at) ON pagi_messages

-- Follow Check
INDEX(follower_id, following_id) ON pagi_follows
```

### 6.3 Cache & In-Memory Store (Redis)

**Versi**: `redis:alpine`

| Cache Key | TTL | Konten |
|---|---|---|
| `pagi_feed_projects_raw` | Invalidated on write | Feed karya mentah galeri publik |
| `pagi_admin_stats` | Invalidated on write | Statistik ringkasan admin |
| `pagi_explore_people_{moduleId}` | 60 detik | Data halaman People/Komunitas |
| `pagi_gallery_recommended_page_{page}` | 60 detik | Galeri per halaman (sort Recommended) |
| `pagi_people_you_may_know_{moduleId}_{userId}` | 600 detik | Saran pengguna yang mungkin dikenal |

**Penggunaan Redis Lainnya:**

| Driver | Konfigurasi | Keterangan |
|---|---|---|
| **Session** | `SESSION_DRIVER=redis` | Penyimpanan sesi pengguna |
| **Queue** | `QUEUE_CONNECTION=redis` | Antrian tugas background (email, notifikasi) |
| **Rate Limiter** | Throttle berbasis Redis | Melindungi endpoint dari DoS/count inflation |

---

## 7. LIBRARY & API PENDUKUNG

### 7.1 Library PHP (Backend via Composer)

| Library | Versi | Fungsi di Modul PAGI |
|---|---|---|
| `inertiajs/inertia-laravel` | `^2.0` | Adapter Inertia.js untuk Laravel |
| `laravel/scout` | `^11.1` | Full-text search engine adapter |
| `meilisearch/meilisearch-php` | `^1.0` | Driver search engine (Meilisearch) |
| `laravel/reverb` | `^1.10` | WebSocket server untuk chat real-time |
| `laravel/horizon` | `^5.47` | Dashboard monitoring antrian Redis Queue |
| `laravel/pulse` | `^1.7` | Monitoring performa aplikasi real-time |
| `laravel/sanctum` | `^4.0` | Token-based API authentication |
| `laravel/fortify` | `^1.30` | Sistem autentikasi backend (login, register, 2FA) |
| `laravel/socialite` | `^5.27` | OAuth login via Google |
| `laravel/octane` | `*` | High-performance server (FrankenPHP) |
| `intervention/image` | `^3.0` | Kompresi & manipulasi gambar server-side |
| `pbmedia/laravel-ffmpeg` | `^8.9` | Pemrosesan video server-side |
| `barryvdh/laravel-dompdf` | `^3.1` | Ekspor PDF (CV Builder) |
| `mpdf/mpdf` | `^8.1.1` | Ekspor PDF alternatif (dukungan Unicode) |
| `mews/purifier` | `^3.4` | Sanitasi HTML (XSS protection) konten rich-text |
| `phpoffice/phpspreadsheet` | `^5.7` | Ekspor data ke Excel (admin analitik) |
| `bacon/bacon-qr-code` | `^3.1` | Generasi QR Code server-side |
| `pragmarx/google2fa` | `^9.0` | Two-Factor Authentication (TOTP) |
| `minishlink/web-push` | `^10.1` | Web Push Notifications (PWA) |
| `jenssegers/agent` | `^2.6` | Deteksi user agent & device |
| `laravel/wayfinder` | `^0.1.9` | Generate typed route helpers untuk TypeScript |

### 7.2 API Eksternal

| API | Endpoint | Penggunaan | Lokasi Integrasi |
|---|---|---|---|
| **Google Gemini AI (Text)** | `generativelanguage.googleapis.com/v1beta/models/{model}:generateContent` | Moderasi konten teks secara AI | `ContentModerationService::evaluateWithGoogleGeminiApi()` |
| **Google Gemini Vision** | `generativelanguage.googleapis.com/v1beta/models/{model}:generateContent` | Analisis gambar multimodal (NSFW/berbahaya) | `ContentModerationService::evaluateImageWithGoogleGeminiApi()` |
| **Google Gemini ListModels** | `generativelanguage.googleapis.com/v1beta/models` | Ambil daftar model AI yang tersedia | `ContentModerationService::fetchAvailableGeminiModels()` |
| **Google OAuth 2.0** | `accounts.google.com` | Login/registrasi via akun Google | Auth module (Socialite) |
| **Meilisearch** | Self-hosted instance | Full-text search karya | `PagiSocialService::buildGalleryQuery()` |

### 7.3 Konfigurasi Google Gemini

| Parameter | Nilai |
|---|---|
| **Model Default Text** | `gemini-1.5-flash` (konfigurasi via admin dashboard) |
| **Model Default Vision** | `gemini-1.5-flash` (multimodal) |
| **Timeout Text Request** | 4 detik |
| **Timeout Vision Request** | 30 detik |
| **Fallback** | Jika API key tidak ada atau timeout -> mesin heuristik lokal |

---

## 8. INFRASTRUKTUR PENGEMBANGAN

### 8.1 Lingkungan Pengembangan Lokal

| Komponen | Tool | Keterangan |
|---|---|---|
| **Web Server (Lokal)** | Laravel Herd (macOS) | Runtime PHP via Valet-based server |
| **Build Tool Frontend** | Vite `^7.0.4` | HMR (Hot Module Replacement) untuk Vue |
| **Package Manager PHP** | Composer `v2` | Manajemen dependensi PHP |
| **Package Manager JS** | npm (Node.js 22) | Manajemen dependensi JavaScript |
| **TypeScript Checker** | `vue-tsc ^3.3.8` | Type checking TypeScript di Vue SFC |
| **Code Formatter JS/TS** | Biome `^2.4.10` | Format & lint JS/TS (pengganti ESLint+Prettier) |
| **PHP Code Style** | Laravel Pint `^1.24` | Code style checker & formatter PHP |
| **PHP Static Analyzer** | Larastan `^3.10` (PHPStan) | Analisis statis PHP untuk keamanan tipe |
| **Debugger** | Laravel Telescope `^5.20` | Debug panel Laravel (dev only) |
| **Log Viewer** | Laravel Pail `^1.2.2` | Real-time log tailing di terminal |

### 8.2 Containerisasi Pengembangan (Laravel Sail)

Docker Compose (`compose.yaml`) untuk pengembangan via Docker:

| Service | Image | Port |
|---|---|---|
| `laravel.test` | PHP 8.5 (Sail runtime) | 80, 5173 |
| `mysql` | MySQL 8.4 | 3306 |
| `redis` | Redis Alpine | 6379 |
| `mailpit` | axllent/mailpit | 1025, 8025 |

### 8.3 Framework Testing

| Jenis Test | Framework | Command |
|---|---|---|
| **Unit & Feature Test (PHP)** | PestPHP `^4.4` | `php artisan test` |
| **E2E Test (Browser)** | Playwright `^1.61.1` | `npm run test:e2e` |
| **E2E Auth** | Playwright | `npm run test:e2e:auth` |
| **E2E Admin** | Playwright | `npm run test:e2e:admin` |
| **E2E Security** | Playwright | `npm run test:e2e:security` |
| **E2E UI** | Playwright | `npm run test:e2e:ui-tests` |
| **E2E Performance** | Playwright | `npm run test:e2e:performance` |
| **Multi-Browser** | Playwright | Chromium, Firefox, WebKit |
| **Mobile Testing** | Playwright | iPhone 15, Pixel 8 (emulated) |

### 8.4 Optimasi Vite Build

**Build Target**: `es2022` | **SSR**: `vite build --ssr` (opsional)

**Manual Chunk Splitting:**

| Chunk | Konten | Tujuan |
|---|---|---|
| `vue-runtime` | Vue 3 core, @inertiajs, axios | Core runtime selalu dimuat |
| `chart-vendor` | ApexCharts, vue3-apexcharts | Diload hanya di halaman analitik |
| `editor-core` | EditorJS core | Diload hanya di halaman editor |
| `editor-media` | EditorJS image, attaches, embed, link | Editor media tools |
| `editor-code` | EditorJS code, table | Editor code tools |
| `editor-basic` | EditorJS paragraph, header, list, quote, dll | Editor basic tools |
| `editor-inline` | EditorJS inline tools | Editor inline formatting |
| `ffmpeg` | @ffmpeg/* (WebAssembly) | Video processing (diload on-demand) |

**Plugin Vite Kustom:**
- `updateSwCacheVersion()` — Update cache version PWA setiap build
- `copyFFmpegCore()` — Salin `ffmpeg-core.js` & `ffmpeg-core.wasm` ke `public/`

### 8.5 CI/CD & Code Quality

| Tool | File Konfigurasi | Fungsi |
|---|---|---|
| GitHub Actions | `.github/` | Otomatisasi CI pipeline |
| CodeRabbit | `.coderabbit.yaml` | AI code review otomatis di Pull Request |
| PHPStan/Larastan | `phpstan.neon` + `phpstan-baseline.neon` | Analisis statis PHP level tinggi |
| SonarScanner | `sonar-scanner.sh` | Analisis kualitas kode & keamanan |
| Biome | `biome.json` | Linting & formatting JS/TS |
| Playwright | `playwright.config.ts` | Konfigurasi E2E testing multi-browser |

---

## 9. INFRASTRUKTUR DEPLOYMENT

### 9.1 Stack Teknologi Produksi

| Komponen | Teknologi | Keterangan |
|---|---|---|
| **Container Runtime** | Docker (Alpine Linux) | Container ringan berbasis Alpine |
| **PHP Runtime** | `php:8.4-fpm-alpine` | PHP 8.4 via FPM |
| **Web Server** | Nginx | Reverse proxy & static file serving |
| **High-Performance Server** | FrankenPHP v1.5.0 | Server PHP modern berbasis Caddy (Laravel Octane) |
| **Process Manager** | Supervisor | Mengelola Nginx, Queue Worker, Reverb, Cron, Pulse |
| **Database** | MySQL `8.0` | Persistence data |
| **Cache/Queue/Session** | Redis Alpine | Cache, session, antrian tugas |
| **PaaS Deployment** | Dokploy | Self-hosted PaaS berbasis Docker + Traefik |
| **Reverse Proxy/SSL** | Traefik (dari Dokploy) | SSL termination + WebSocket upgrade (wss://) |
| **DNS/SSL Certificate** | Let's Encrypt via Traefik | Sertifikat SSL otomatis & gratis |

### 9.2 Multi-Stage Dockerfile (3 Tahap Build)

```
Stage 1: composer:2 (Composer Builder)
   - Install PHP dependencies (no dev)
   - Dump optimized autoloader (classmap)
   - Generate Wayfinder typed route files
        |
        v
Stage 2: node:22-alpine (Frontend Builder)
   - npm ci --legacy-peer-deps
   - Salin Wayfinder files dari Stage 1
   - npm run build (Vite: JS/CSS/WASM assets)
        |
        v
Stage 3: php:8.4-fpm-alpine (Production Runtime)
   - Install system packages: ffmpeg, nginx, supervisor, git
   - Install PHP extensions: pdo_mysql, bcmath, gd, zip,
     opcache, pcntl, redis, exif
   - Salin: kode aplikasi, vendor/, public/build/
   - Install/salin FrankenPHP binary
   - Set entrypoint.sh sebagai ENTRYPOINT
```

### 9.3 Layanan dalam Container Produksi (Supervisor)

| Proses | Port | Keterangan |
|---|---|---|
| **FrankenPHP / Nginx + PHP-FPM** | 80 | Web server utama (public) |
| **Laravel Reverb** | 8080 (internal) | WebSocket server untuk chat real-time |
| **Laravel Queue Worker** | — | Proses antrian background (email, notifikasi) |
| **Laravel Scheduler (Crond)** | — | Tugas terjadwal (cleanup, statistik periodik) |
| **Laravel Pulse** | — | Monitoring performa aplikasi real-time |

### 9.4 Docker Compose Produksi (`docker-compose.prod.yml`)

```yaml
Services:
  app (fmikom_app):
    image: fmikom-portal:latest
    ports: "80:80"
    volumes: app_storage:/var/www/html/storage/app
    depends_on: [mysql, redis]
    env: APP_ENV=production, SESSION_DRIVER=redis
         QUEUE_CONNECTION=redis, CACHE_STORE=redis

  mysql (fmikom_db):
    image: mysql:8.0
    volumes: db_data:/var/lib/mysql
    port: 127.0.0.1:3306:3306 (hanya loopback, tidak publik)

  redis (fmikom_redis):
    image: redis:alpine
    volumes: redis_data:/data

Persistent Volumes:
  app_storage  <- Media portofolio, sertifikat, foto profil
  db_data      <- Data MySQL (jangan dihapus saat re-deploy)
  redis_data   <- Data Redis persistent
```

### 9.5 Health Check & Zero-Downtime

```dockerfile
HEALTHCHECK --interval=5s --timeout=3s --start-period=45s --retries=3 \
  CMD curl -f http://localhost:80/up || exit 1
```

### 9.6 Spesifikasi Minimum Server VPS

| Komponen | Minimum | Rekomendasi |
|---|---|---|
| **OS** | Ubuntu 22.04 LTS / Debian 12 | Ubuntu 24.04 LTS |
| **CPU** | 2 vCPU Core | 4 vCPU Core |
| **RAM** | 2 GB | 4 GB + 2 GB Swap |
| **Storage** | 20 GB SSD | 50 GB NVMe |
| **Network** | 100 Mbps | 1 Gbps |

### 9.7 Proses Entrypoint Otomatis (`docker/entrypoint.sh`)

Script dijalankan setiap kali container dimulai:

1. Menunggu MySQL siap (grace period 30 detik)
2. Membuat symbolic link storage publik (`php artisan storage:link`)
3. Set permission folder `storage/` dan `bootstrap/cache/`
4. Menjalankan migrasi database otomatis (`php artisan migrate --force`) jika `RUN_MIGRATIONS=true`
5. Cache konfigurasi, route, dan view (`php artisan optimize`)
6. Memulai Supervisor (mengelola semua proses)

---

## 10. KEAMANAN DAN AUTENTIKASI

### 10.1 Sistem Autentikasi

| Mekanisme | Library/Tool | Fungsi |
|---|---|---|
| **Session-Based Auth** | Laravel Fortify + Auth | Login/logout berbasis session cookie |
| **OAuth 2.0 (Google)** | Laravel Socialite `^5.27` | Login via akun Google kampus |
| **Two-Factor Authentication (2FA)** | `pragmarx/google2fa ^9.0` | TOTP berbasis aplikasi authenticator |
| **API Token** | Laravel Sanctum `^4.0` | Token untuk akses API terprogram |

### 10.2 Role-Based Access Control (RBAC) Modul PAGI

Akses modul PAGI dikontrol oleh middleware `module.context:pagi` dengan hierarki role:

| Role | Level | Akses |
|---|---|---|
| **Mahasiswa** (default) | 1 | Dashboard, editor, galeri, chat, profil, notifikasi |
| **Dosen / Staff** | 2 | Dashboard, galeri (view & interaksi saja) |
| **`prodi`** | 3 | Semua akses admin dasar |
| **`admin-akademik`** | 4 | Semua akses admin + moderasi |
| **`admin-universitas`** | 5 | Semua akses admin + moderasi + manajemen pengguna |
| **`admin`** | 6 | Akses admin penuh |
| **`super-admin`** | 7 | Akses tak terbatas |

### 10.3 Keamanan Data & Konten

| Mekanisme | Implementasi | Tujuan |
|---|---|---|
| **CSRF Protection** | Laravel CSRF Token (semua POST/PUT/PATCH/DELETE) | Mencegah Cross-Site Request Forgery |
| **XSS Prevention (Server)** | `mews/purifier` (HTMLPurifier) di `PortfolioService::sanitizeHtmlContent()` | Membersihkan HTML berbahaya dari konten rich-text |
| **XSS Prevention (Client)** | DOMPurify `^3.4.5` | Sanitasi HTML di sisi browser sebelum dirender |
| **SQL Injection** | Eloquent ORM + Query Builder (parameter binding) | Seluruh query menggunakan prepared statements |
| **Rate Limiting (Throttle)** | Laravel Rate Limiter berbasis Redis | Melindungi endpoint dari DoS dan count inflation |
| **Moderasi Konten Teks** | `ContentModerationService::scan()` | Deteksi kata terlarang (kamus lokal + Google Gemini AI) |
| **Moderasi Konten Gambar** | `ContentModerationService::scanImage()` | Deteksi NSFW (GD heuristik + Gemini Vision API) |
| **End-to-End Encryption Chat** | Public Key Exchange via `/pagi/messages/pubkey` | Enkripsi pesan di client sebelum dikirim ke server |
| **Virus Scan** | `VirusScannerService` (di `PagiCertificateService`) | Pemindaian malware pada file yang diunggah |
| **Secure Cookie** | `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true` | Cookie session hanya via HTTPS, payload terenkripsi |
| **File Type Validation** | Validasi MIME type + ekstensi + ukuran di Form Request | Mencegah upload file berbahaya |

### 10.4 Keamanan Infrastruktur

| Mekanisme | Implementasi |
|---|---|
| **SSL/TLS** | Let's Encrypt via Traefik (otomatis, produksi) |
| **HTTPS Only** | `SESSION_SECURE_COOKIE=true`, `APP_URL=https://` |
| **WebSocket Secure** | `wss://` via Traefik WebSocket upgrade (port 443) |
| **APP_DEBUG=false** | Debug mode dimatikan di produksi (tidak ada stack trace publik) |
| **Database Isolation** | MySQL hanya diekspos ke `127.0.0.1` (tidak publik ke internet) |
| **Environment Secrets** | Semua credential via environment variables (tidak di-hardcode di kode) |
| **Docker Network Isolation** | Semua container dalam internal Docker network |
| **Dependency Security Audit** | `enlightn/security-checker` (dev tool) + GitHub Security Advisory |

### 10.5 Perlindungan Endpoint Spesifik

| Endpoint | Proteksi yang Diterapkan |
|---|---|
| `POST /pagi/editor` | auth + module.context + throttle:uploads + XSS sanitasi + moderasi konten AI |
| `POST /pagi/works/report` | auth + module.context + CSRF |
| `POST /pagi/messages` | auth + throttle:pagi-chat-send + CSRF |
| `POST /pagi/preview/{id}/like` | auth + throttle:30,1 (max 30 req/menit) |
| `POST /pagi/preview/{id}/comment` | auth + throttle:20,1 (max 20 req/menit) |
| `DELETE /pagi/admin/reset-all-works` | auth + admin role + password re-confirm + text confirm (double verification) |
| `GET /pagi/cv/{cv}/shared` | auth (SEC-004: mencegah IDOR enumeration CV tanpa login) |

### 10.6 Sistem Moderasi Konten Berlapis (5 Layer)

```
Input Konten (Teks / Gambar)
       |
       v
Layer 1: Local Banned Words Dictionary
  - Kamus 6 kategori:
    * judi_online (slot, gacor, maxwin, scatter, dll.)
    * profanity (kata kasar Bahasa Indonesia)
    * harassment (pelecehan/bullying)
    * sexual (konten vulgar)
    * threat (ancaman kekerasan)
    * phishing (penipuan/scam)
  - Deteksi leetspeak (0->o, 1->i, 4->a, @->a, $->s, dll.)
  - Deteksi pemisah tersembunyi (a.n.j.i.n.g -> anjing)
  - Kamus kustom admin (dari portal_settings.pagi_banned_words di DB)
       |
       v
Layer 2: AI Context Heuristics
  - Regex pattern untuk pola ancaman & judi terselubung
  - Contoh pola: "bunuh diri", "tak hajar", "depo \d+", "zeus olympus"
       |
       v
Layer 3: Google Gemini AI Text (opsional, jika API key aktif)
  - REST API call ke Gemini dengan system prompt moderasi kampus
  - Timeout: 4 detik (fallback ke heuristik lokal jika timeout)
  - Respons JSON: {is_toxic, category, reason}
       |
  (Khusus Konten Gambar)
       v
Layer 4: Local GD Heuristic (0 biaya API, analisis pixel)
  - Resize gambar ke 100x100 pixel
  - Analisis pixel-level:
    * skin tone ratio > 0.48 -> NSFW (sexual)
    * blood red ratio > 0.12 -> Violence (threat)
       |
       v
Layer 5: Google Gemini Vision API (opsional, jika API key aktif)
  - Multimodal analysis (gambar Base64 + deskripsi caption)
  - Kategori: sexual, judi_online, threat, phishing + custom admin rules
  - Timeout: 30 detik

Hasil Moderasi:
  - is_flagged: true/false
  - severity: clean / medium / high / critical
  - categories: [kategori pelanggaran terdeteksi]
  - matched_words: [kata/frasa yang terdeteksi]
  - censored_text: teks tersensor (a***ng)
```

---

## RINGKASAN TEKNIS

| Aspek | Nilai |
|---|---|
| **Pola Arsitektur** | Modular Monolith (MVC + Service Layer + Action Pattern + Concern Trait) |
| **Frontend** | Vue 3.5 (Composition API + TypeScript) + Inertia.js v2 + Tailwind CSS v4 |
| **Block Editor** | Tiptap v3 (25+ ekstensi) + Editor.js |
| **Backend** | Laravel 12 (PHP 8.4) |
| **Database** | MySQL 8.0/8.4 + 22 tabel PAGI (prefix: pagi_) |
| **Cache/Queue/Session** | Redis Alpine |
| **Search Engine** | Meilisearch via Laravel Scout |
| **Real-Time** | Laravel Reverb (WebSocket) + Laravel Echo |
| **AI Integration** | Google Gemini AI (Text Moderation + Vision Moderation) |
| **Media Processing** | FFmpeg server-side (pbmedia/laravel-ffmpeg) + WebAssembly FFmpeg (browser) |
| **PDF Generation** | DomPDF + mPDF (CV Builder) |
| **Deployment** | Docker Multi-stage (3 stage) + Dokploy PaaS + Traefik + VPS Ubuntu |
| **Process Manager** | Supervisor (Nginx, FrankenPHP, Reverb, Queue Worker, Cron, Pulse) |
| **Keamanan** | CSRF, XSS Purifier, Rate Limiting, E2E Encryption Chat, AI Content Moderation, VirusScanner |
| **Autentikasi** | Laravel Fortify + Google OAuth 2.0 + 2FA (TOTP) + Sanctum |
| **Otorisasi** | Custom RBAC via middleware module.context + 7 level role |
| **Testing** | PestPHP (unit/feature) + Playwright (E2E: Chromium, Firefox, WebKit, Mobile) |
| **Code Quality** | PHPStan/Larastan + Biome + Pint + CodeRabbit AI Review + SonarScanner |

---

*Dokumen ini disusun berdasarkan analisis mendalam terhadap source code aktual Modul PAGI pada repository FMIKOM Portal.*
*Tanggal: 19 Agustus 2026 | Dibuat untuk keperluan dokumentasi dan laporan sistem internal.*
*Dokumen bersifat RAHASIA dan tidak untuk disebarluaskan ke pihak luar.*
