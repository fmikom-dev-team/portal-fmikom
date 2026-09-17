# ANALISIS SOURCE CODE MODUL PAGI
## PORTFOLIO & GALLERY INTERFACE (PAGI)
### FMIKOM Portal — Fakultas Matematika dan Ilmu Komputer

---

> **Tanggal Analisis**: 21 Agustus 2026
> **Basis Analisis**: Source code aktual (routes, controller, service, model, migration, Vue pages)
> **Tujuan Dokumen**: Bahan mentah penyusunan bagian "Struktur dan Fitur Utama" untuk laporan HKI
> **Catatan Penting**: Seluruh klaim dalam dokumen ini dapat ditelusuri kembali ke file implementasi yang disebutkan.

---

## A. STRUKTUR PENGGUNA / ROLE

### Dasar Implementasi Role

Akses ke modul PAGI dikontrol oleh dua middleware utama:

1. `module.context:pagi` — untuk semua pengguna biasa
2. `module.context:pagi,super-admin,admin,admin-universitas,admin-akademik,prodi` — untuk admin

**Sumber**: `routes/pagi.php`, baris 15 dan 227

Selain itu, pada level controller, middleware inline memeriksa nilai `resolved_role` dari session/request attribute:
- **Bukti**: `PagiEditorController::middleware()` — memeriksa `strtolower($role) !== 'mahasiswa'`
- **Bukti**: `PagiCvController::middleware()` — memeriksa `!in_array(strtolower($role), ['mahasiswa', 'alumni'])`

| Role | Level | Sumber Verifikasi | Tipe |
|---|---|---|---|
| Mahasiswa | Pengguna biasa | `PagiEditorController::middleware()` | Pengguna |
| Alumni | Pengguna biasa | `PagiCvController::middleware()` (izin alumni) | Pengguna |
| Dosen / Staff | Pengguna biasa (terbatas) | Middleware `module.context:pagi` | Pengguna |
| prodi | Admin | `routes/pagi.php:227` | Admin |
| admin-akademik | Admin | `routes/pagi.php:227` | Admin |
| admin-universitas | Admin | `routes/pagi.php:227` | Admin |
| admin | Admin | `routes/pagi.php:227` | Admin |
| super-admin | Admin penuh | `routes/pagi.php:227` | Admin |

---

### Role 1: Mahasiswa

**Dasar Implementasi:**
- Middleware: `module.context:pagi`, `throttle:pagi-api`
- Role Check: `strtolower($role) === 'mahasiswa'` di `PagiEditorController::middleware()`
- Route: Semua route dalam group `prefix('pagi')`, baris 15–223 `routes/pagi.php`

**Hak Akses (terverifikasi dari route dan controller):**
- Dashboard & Feed karya (GET `/pagi/`) — `PagiDashboardController::index()`
- Membuat karya portofolio (GET/POST `/pagi/editor`) — `PagiEditorController::editor()` dan `store()`
- Mengedit dan menghapus karya milik sendiri — `PagiEditorController::update()`, `destroy()`
- Mempublikasikan karya via galeri cepat (POST `/pagi/gallery/store`) — `PagiEditorController::storeGalleryItem()`
- Menjelajah galeri karya semua pengguna (GET `/pagi/gallery`) — `PagiDashboardController::exploreGallery()`
- Menjelajah halaman komunitas/People (GET `/pagi/people`) — `PagiDashboardController::explorePeople()`
- Berinteraksi: Like karya, komentar, reply, like komentar/reply
- Follow/unfollow pengguna lain (POST `/pagi/users/{user}/follow`)
- Direct message (seluruh route `/pagi/messages/*`)
- Mengelola profil publik (GET/POST `/pagi/profile/edit`)
- Mengelola sertifikat dan riwayat pendidikan
- Menggunakan CV Builder (GET/POST `/pagi/cv/*`)
- Melihat dan mengelola notifikasi
- Melaporkan karya orang lain

**Tidak Dapat Diakses:**
- Semua route `/pagi/admin/*`
- Aksi moderasi (hide, warn, takedown)
- Melihat daftar laporan dari seluruh pengguna

---

### Role 2: Alumni

**Dasar Implementasi:**
- Middleware inline: `PagiCvController::middleware()` → `in_array(strtolower($role), ['mahasiswa', 'alumni'])`
- Route: `/pagi/cv/*` (seluruh route CV Builder)

**Hak Akses (terverifikasi):**
- Menggunakan CV Builder (membuat, mengedit, mengunduh PDF CV)
- Akses halaman PAGI umum (galeri, profil, notifikasi)

**Tidak Dapat Diakses:**
- Pembuatan karya melalui Editor (Editor hanya untuk mahasiswa)
- Semua route admin

---

### Role 3: Dosen / Staff

**Dasar Implementasi:**
- Middleware: `module.context:pagi` — mendapat akses ke route user biasa
- Tidak ada middleware role khusus yang membatasi akses ke editor di route level (pembatasan ada di controller)

**Hak Akses (terverifikasi):**
- Melihat dashboard dan galeri
- Melihat profil publik pengguna
- Berinteraksi (like, komentar) berdasarkan middleware yang tidak membatasinya
- Menerima notifikasi

**Tidak Dapat Diakses:**
- Editor karya (dibatasi di `PagiEditorController::middleware()`)
- CV Builder (dibatasi di `PagiCvController::middleware()`)
- Semua route admin

---

### Role 4–8: Admin (prodi, admin-akademik, admin-universitas, admin, super-admin)

**Dasar Implementasi:**
- Middleware: `module.context:pagi,super-admin,admin,admin-universitas,admin-akademik,prodi`
- Route: Semua route `prefix('pagi/admin')`, baris 227–349 `routes/pagi.php`

**Hak Akses (terverifikasi dari route):**
- Dashboard statistik (`GET /pagi/admin/`)
- Analitik (`GET /pagi/admin/analytics`)
- Manajemen laporan: lihat, tindak, arsipkan
- Moderasi konten: sembunyikan, hapus, restore karya
- Peringatan pengguna: kirim peringatan, cabut peringatan
- Manajemen pengguna: ubah status, kirim notifikasi
- Manajemen showcase/pameran karya
- Pengaturan kamus kata terlarang
- Pengaturan kamus moderasi gambar
- Pengaturan umum modul
- Reset data moderasi (berbahaya, dilindungi verifikasi ganda)

---

## B. STRUKTUR MODUL

| Modul | Fitur Utama | Pengguna |
|---|---|---|
| A. Portofolio & Karya | Buat, edit, hapus, publikasi karya | Mahasiswa |
| B. Galeri / Eksplorasi | Jelajah karya semua pengguna, filter, sort, search | Semua role |
| C. Profil | Profil publik, edit profil, reorder karya, username PAGI | Semua role |
| D. Interaksi Sosial | Like, komentar, reply, like komentar, follow, laporan | Semua role |
| E. Komunikasi (Chat) | Direct message real-time, reaksi, pin, arsip, blokir | Semua role |
| F. CV Digital | Builder CV, template, unduh PDF, duplikasi | Mahasiswa, Alumni |
| G. Sertifikat & Pendidikan | CRUD sertifikat, CRUD pendidikan, upload media | Mahasiswa |
| H. Notifikasi | Pusat notifikasi, mark read, hapus | Semua role |
| I. Moderasi | Laporan karya, peringatan, hide/restore, AI moderasi | Admin |
| J. Showcase | Pameran karya pilihan, toggle showcase | Admin |
| K. Administrasi | Manajemen pengguna, pengaturan modul | Admin |
| L. Analitik | Statistik, grafik, chart data | Admin |

---

## C. DETAIL SETIAP FITUR

---

### [C.1] Pembuatan Karya Portofolio (Melalui Editor Blok)

**Pengguna:** Mahasiswa
**Tujuan:** Membuat karya digital terstruktur (portofolio) yang dapat dipublikasikan ke galeri umum FMIKOM.
**Fungsi:** Mahasiswa menyusun karya menggunakan block editor (Tiptap), menambahkan gambar/video sampul, mengisi metadata (judul, kategori, tag, tools, deskripsi), menambahkan kolaborator, kemudian memilih apakah karya dipublikasikan atau disimpan sebagai draft.

**Route:**
- `GET /pagi/editor` → buka editor kosong atau edit karya existing (`?id={id}`)
- `POST /pagi/editor` → simpan/publikasi karya baru

**Frontend:**
- File: `resources/js/pages/Modules/Pagi/User/Editor/Editor.vue` (23.8 KB)
- Canvas editor: `EditorCanvas.vue` (25.1 KB)
- Sidebar blok: `EditorSidebar.vue` (9.8 KB)
- Modal publikasi: `EditorPublishModal.vue` (22.1 KB)
- Toolbar mobile: `EditorMobileBar.vue` (13.6 KB)
- Wrapper Tiptap: `PagiTiptapEditor.vue` (10.6 KB)
- Composable upload: `useEditorFileUpload.ts` (16.2 KB)
- Composable draft: `useEditorDraft.ts`
- Composable tags: `useEditorTags.ts`
- Composable kolaborator: `useEditorCollaborators.ts`

**Controller:** `PagiEditorController::editor()`, `store()`
**Service:** `PortfolioService::processContentBlocks()`, `saveCoverImage()`, `processAndNotifyCollaborators()`, `notifyFollowers()`
**Model:** `PagiWork`
**Database:** `pagi_works`

**Input:**
- `title` (string, max 255, wajib jika published)
- `content` (array JSON block, nullable)
- `cover_image` (file: jpeg, jpg, png, gif, webp, avif, heic, heif, svg, bmp, mp4, mov, qt, avi, webm, mkv, 3gp, wajib jika published)
- `category` (string, max 100, wajib jika published)
- `tags` (string, wajib jika published)
- `tools_used` (string, max 255, wajib jika published)
- `description` (string, max 2000, wajib jika published)
- `visibility` (enum: Everyone, Private, wajib jika published)
- `is_published` (boolean)
- `collaborators` (array of `{id}`, nullable)

**Output:**
- Record baru di `pagi_works`
- Cache `pagi_feed_projects_raw` dihapus otomatis (Model booted event)
- Notifikasi dikirim ke followers (jika dipublikasikan)
- Notifikasi dikirim ke kolaborator yang diundang
- Event `PagiWorkCreated` didispatch
- Jika terdeteksi AI moderation: status diubah ke `review`, karya tidak dipublikasikan, laporan otomatis dibuat di `pagi_reports`

**Validasi (dari `StorePortfolioRequest`):**
- `title`: required jika published, max 255
- `cover_image`: required jika published; validasi file, extensions, max size dari `PortalSetting.pagi_max_upload_size_mb`
- `category`: required jika published
- `tags`: required jika published
- `tools_used`: required jika published
- `description`: required jika published, max 2000
- `visibility`: required jika published, in: Everyone, Private
- `collaborators.*.id`: nullable, exists:users,id
- `content.*.file`: validasi extensions dan max size
- Custom rule `VideoDurationRule` untuk file video

**Status:**
- `active` — karya aktif, tampil di galeri
- `review` — karya terkena moderasi otomatis, menunggu tinjauan admin
- `warning` — karya mendapat peringatan dari admin
- `hidden` — karya disembunyikan oleh admin
- `removed` — karya dihapus oleh admin

**Pembatasan Akses:**
- Middleware `throttle:uploads` pada POST `/pagi/editor`
- Middleware inline: hanya `mahasiswa` yang dapat mengakses; akun suspended tidak dapat melakukan aksi POST

**Tata Cara Penggunaan:**
1. Pengguna login dan membuka modul PAGI.
2. Pengguna memilih menu Editor dari navigasi.
3. Sistem membuka halaman editor dengan canvas kosong.
4. Pengguna menyusun konten menggunakan blok (teks kaya, gambar, video, tabel, kode, dll) di Tiptap Editor.
5. Pengguna mengisi sidebar: judul, deskripsi, kategori, tag, tools, kolaborator.
6. Pengguna mengunggah gambar atau video sampul.
7. Pengguna memilih "Publish" atau "Simpan sebagai Draft".
8. Sistem melakukan validasi input.
9. Sistem memproses blok konten melalui `PortfolioService::processContentBlocks()`.
10. Sistem memindai konten melalui `ContentModerationService::scan()` dan `scanImage()`.
11. Jika konten aman: karya disimpan, notifikasi dikirim, event didispatch, pengguna diarahkan ke dashboard.
12. Jika terdeteksi pelanggaran: karya masuk status `review` dan tidak dipublikasikan.

**Hubungan dengan Fitur Lain:**
- Karya yang published muncul di Galeri dan Dashboard feed
- Kolaborator menerima notifikasi → dapat accept/decline
- Followers menerima notifikasi new work
- AI moderasi terhubung ke sistem laporan admin

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/PagiEditorController.php`, method `store()` baris 128–186
- File: `app/Modules/Pagi/Requests/StorePortfolioRequest.php`
- File: `app/Modules/Pagi/Services/PortfolioService.php`
- Migration: `database/migrations/pagi/2026_06_06_070847_rename_pagi_portfolios_to_pagi_works.php`

---

### [C.2] Pembuatan Karya Cepat (Quick Store)

**Pengguna:** Mahasiswa
**Tujuan:** Membuat karya dengan isian minimal melalui modal di halaman dashboard — lebih cepat dari Editor penuh.
**Fungsi:** Berbeda dari editor penuh, quick store mengisi field terbatas (judul, cover, skills, tools, kolaborator, link karya, client, tanggal) dan langsung mempublikasikan karya.

**Route:**
- `POST /pagi/editor/quick-store` → `PagiEditorController::quickStore()`

**Input:**
- `title` (string)
- `cover_image` (file)
- `skills` (JSON string → array)
- `tools` (JSON string → array)
- `collaborators` (JSON string → array)
- `completed_work_link` (string, nullable)
- `client` (string, nullable)
- `start_date`, `end_date` (string, nullable)
- `industry` (string, nullable)
- `original_work_confirmed` (boolean)
- `cover_fit` (string, default: cover)

**Output:**
- Record baru di `pagi_works` dengan `is_published=true`
- Konten disimpan dalam format blok `featured_details`
- Response JSON berisi data karya yang baru dibuat (untuk update UI tanpa reload)

**Validasi (dari `QuickStorePortfolioRequest`):**
- `title`: required
- `cover_image`: required, file, extensions terbatas

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/PagiEditorController.php`, method `quickStore()` baris 188–262
- File: `app/Modules/Pagi/Requests/QuickStorePortfolioRequest.php`

---

### [C.3] Penambahan Item Galeri (Gallery Item)

**Pengguna:** Mahasiswa
**Tujuan:** Menambahkan item galeri sederhana (satu gambar + judul + deskripsi) ke profil, berbeda dari karya portofolio yang memiliki konten blok penuh.

**Route:** `POST /pagi/gallery/store` → `PagiEditorController::storeGalleryItem()`

**Input:**
- `title` (string, nullable)
- `cover_image` (file, wajib)
- `description` (string, nullable)

**Output:**
- Record baru di `pagi_works` dengan blok tipe `gallery_item`
- Response JSON berisi data item galeri

**Validasi (dari `StoreGalleryItemRequest`):**
- `cover_image`: required, extensions gambar

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/PagiEditorController.php`, method `storeGalleryItem()` baris 530–581

---

### [C.4] Edit Karya Portofolio

**Pengguna:** Mahasiswa (pemilik karya)
**Tujuan:** Memperbarui konten, metadata, atau cover gambar karya yang sudah dibuat.

**Route:** `POST /pagi/editor/{editor}` → `PagiEditorController::update()`

**Input:** Sama dengan `StorePortfolioRequest` (via `UpdatePortfolioRequest`)

**Output:**
- Record di `pagi_works` diperbarui
- Jika karya sebelumnya ber-status `warning` atau `hidden`, status diubah ke `pending` dan laporan Re-Review dibuat
- Event `PagiWorkUpdated` didispatch

**Pembatasan:**
- Karya yang ber-status `hidden` atau `removed` tidak dapat diedit (redirect dengan pesan peringatan)

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/PagiEditorController.php`, method `update()` baris 281–351

---

### [C.5] Hapus Karya

**Pengguna:** Mahasiswa (pemilik karya)
**Tujuan:** Menghapus karya dari sistem.

**Route:** `DELETE /pagi/editor/{editor}` → `PagiEditorController::destroy()`

**Output:**
- Record dihapus dari `pagi_works`
- Laporan `pending` terkait karya ditandai `actioned` dengan catatan "dihapus oleh pemilik"
- Event `PagiWorkDeleted` didispatch
- Cache profil dan notifikasi dihapus

**Validasi:**
- Hanya pemilik (user_id === Auth::id()) yang dapat menghapus

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/PagiEditorController.php`, method `destroy()` baris 353–380

---

### [C.6] Kolaborasi Karya

**Pengguna:** Mahasiswa (pemilik karya sebagai pengundang; kolaborator sebagai yang diundang)
**Tujuan:** Mendaftarkan kontributor lain dalam sebuah karya. Kolaborator dapat menerima atau menolak undangan.

**Route:**
- Undangan dibuat saat `store()` atau `update()` karya
- `POST /pagi/editor/{editor}/collaboration/accept` — terima undangan
- `POST /pagi/editor/{editor}/collaboration/decline` — tolak undangan
- `POST /pagi/editor/{editor}/collaboration/leave` — keluar dari kolaborasi

**Proses:**
1. Pemilik karya mengisi daftar kolaborator saat membuat/mengedit karya.
2. `PortfolioService::processAndNotifyCollaborators()` mencocokkan ID atau username ke User.
3. Notifikasi dikirim ke setiap kolaborator yang ditemukan.
4. Data kolaborator disimpan dalam blok `featured_details` di kolom `content` (JSON).
5. Kolaborator membuka notifikasi dan memilih accept/decline.
6. Status kolaborator dalam blok content diperbarui.
7. Notifikasi konfirmasi dikirim ke pemilik.

**Data kolaborator dalam JSON `content`:**
```json
{
  "type": "featured_details",
  "collaborators": [
    {"user_id": 5, "name": "Budi", "status": "accepted"}
  ]
}
```

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/PagiEditorController.php`, method `acceptCollaboration()`, `declineCollaboration()`, `leaveCollaboration()`

---

### [C.7] Galeri Eksplorasi Karya

**Pengguna:** Semua role yang terdaftar di modul PAGI
**Tujuan:** Halaman untuk menjelajah seluruh karya yang dipublikasikan oleh semua mahasiswa, dengan fitur filter, sorting, dan pencarian.

**Route:** `GET /pagi/gallery` → `PagiDashboardController::exploreGallery()`

**Frontend:**
- File: `resources/js/pages/Modules/Pagi/User/Gallery.vue` (28.3 KB)

**Proses:**
1. Sistem mengambil data galeri dari cache `pagi_gallery_recommended_page_{page}` (TTL 60 detik) atau dari database.
2. Data hanya berisi karya dengan `is_published=true`, status `active`, visibility `Everyone` atau `null`.
3. Tiptap content dan komentar **tidak** dimuat saat halaman dibuka (Lazy Loading).
4. Ketika pengguna membuka modal pratinjau karya, data lengkap dimuat via `GET /pagi/preview/{id}/data`.

**Data yang dimuat awal (via Inertia):**
- Daftar karya (cover, judul, user, likes, views, status)

**Data Lazy Load (via fetch saat modal dibuka):**
- Konten blok lengkap
- Komentar + balasan
- Data kolaborator

**Pencarian (Meilisearch):**
- Endpoint: `GET /pagi/instant-search?q={query}` → `PagiDashboardController::instantSearch()`
- `PagiWork::search($query)` — menggunakan Laravel Scout dengan Meilisearch
- Field yang diindeks: `title`, `description`, `category`, `tools_used`, `user_name`, `username`
- Hanya karya published, non-private, non-hidden yang diindeks (`shouldBeSearchable()`)

**Bukti Implementasi:**
- File: `app/Models/Pagi/PagiWork.php`, method `toSearchableArray()` baris 44–58, `shouldBeSearchable()` baris 63–68
- File: `app/Modules/Pagi/Services/PagiSocialService.php`

---

### [C.8] Dashboard Feed Mahasiswa

**Pengguna:** Mahasiswa
**Tujuan:** Halaman utama setelah login — menampilkan feed karya, statistik pribadi, daftar karya milik sendiri, dan akses cepat ke fitur PAGI.

**Route:** `GET /pagi/` → `PagiDashboardController::index()`

**Frontend:**
- File: `resources/js/pages/Modules/Pagi/User/MahasiswaDashboard.vue` (45 KB)

**Data yang ditampilkan:**
- Karya milik sendiri
- Feed karya terbaru dari platform (dari cache `pagi_feed_projects_raw`)
- Statistik (jumlah karya, views, likes, followers)
- Karya yang sedang bermasalah (status warning/review)

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/PagiDashboardController.php`, method `index()`

---

### [C.9] Pratinjau Karya (Modal Preview)

**Pengguna:** Semua role
**Tujuan:** Melihat detail karya — konten blok penuh, komentar, informasi penulis — tanpa meninggalkan halaman saat ini.

**Route (Lazy Load):** `GET /pagi/preview/{preview}/data` → `PagiDashboardController::previewData()`

**Pembatasan:** `throttle:60,1` (max 60 request per menit)

**Data yang dikembalikan:**
- Konten blok lengkap (array JSON)
- Komentar beserta balasan dan data like (dengan eager loading penuh)
- Informasi penulis

**Pencatat Tayangan:** `POST /pagi/preview/{preview}/view` → `PagiDashboardController::viewPreview()`, throttle:60,1

**Bukti Implementasi:**
- File: `routes/pagi.php`, baris 213–218
- File: `app/Modules/Pagi/Controllers/PagiDashboardController.php`, method `previewData()`

---

### [C.10] Like Karya

**Pengguna:** Semua role
**Tujuan:** Menyatakan apresiasi terhadap sebuah karya (toggle).

**Route:** `POST /pagi/preview/{preview}/like` → `PagiDashboardController::likePreview()` → `LikeWorkAction::execute()`
**Pembatasan:** `throttle:30,1` (max 30/menit — BUG-FE-001 mitigation dari komentar kode)

**Proses:**
1. Pengguna menekan tombol like pada karya.
2. Request dikirim ke server.
3. `LikeWorkAction::execute()` memanggil `$work->likesRelation()->toggle($authUser->id)`.
4. Toggle menambah atau menghapus record di `pagi_work_likes`.
5. Data like terbaru dikembalikan ke frontend.

**Database:** Tabel `pagi_work_likes` (unique constraint pada `work_id, user_id`)

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Actions/LikeWorkAction.php`
- Migration: `database/migrations/pagi/2026_06_15_000000_create_pagi_work_normalized_tables.php`

---

### [C.11] Komentar dan Balasan

**Pengguna:** Semua role
**Tujuan:** Memberikan komentar tertulis pada karya, dengan dukungan balasan (reply) bersarang.

**Route:**
- `POST /pagi/preview/{preview}/comment` → `CreateCommentAction::execute()`, throttle:20,1
- `POST /pagi/preview/{preview}/comment/{comment}/reply` → `ReplyCommentAction::execute()`, throttle:20,1

**Proses:**
1. Pengguna mengetik komentar dan mengirim.
2. Sistem memindai teks komentar via `ContentModerationService::scan()`.
3. Jika terdeteksi kata terlarang: respons 422 atau teks disensor (bergantung `pagi_comment_censor_mode`).
4. Komentar/balasan disimpan ke `pagi_work_comments` dengan `parent_id = null` (komentar) atau `parent_id = {comment_id}` (balasan).
5. Data komentar terbaru dikembalikan ke frontend.

**Input:**
- `body` (string, required, max:4000)

**Database:** Tabel `pagi_work_comments` (kolom: id, uuid, work_id, user_id, parent_id, body)

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Actions/CreateCommentAction.php`
- File: `app/Modules/Pagi/Actions/ReplyCommentAction.php`
- Migration: `database/migrations/pagi/2026_06_15_000000_create_pagi_work_normalized_tables.php`

---

### [C.12] Like Komentar dan Like Balasan

**Pengguna:** Semua role
**Tujuan:** Mengapresiasi komentar atau balasan tertentu (toggle).

**Route:**
- `POST /pagi/preview/{preview}/comment/{comment}/like` → `LikeCommentAction::execute()`, throttle:30,1
- `POST /pagi/preview/{preview}/comment/{comment}/reply/{reply}/like` → `LikeReplyAction::execute()`, throttle:30,1

**Proses:**
1. Pengguna menekan tombol like pada komentar.
2. `LikeCommentAction::execute()` memvalidasi bahwa komentar memang milik preview tersebut.
3. Toggle dilakukan via `$comment->likesRelation()->toggle($authUser->id)`.
4. Data like terbaru dikembalikan ke frontend.

**Database:** Tabel `pagi_comment_likes` (unique constraint pada `comment_id, user_id`)

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Actions/LikeCommentAction.php` (seluruh file, 41 baris)
- File: `app/Modules/Pagi/Actions/LikeReplyAction.php`

---

### [C.13] Follow / Unfollow Pengguna

**Pengguna:** Semua role
**Tujuan:** Mengikuti pengguna lain agar karya baru mereka muncul di feed, dan mendapatkan notifikasi.

**Route:** `POST /pagi/users/{user}/follow` → `PagiDashboardController::toggleFollow()` → `FollowUserAction::execute()`

**Proses:**
1. Pengguna menekan tombol follow pada profil pengguna lain.
2. `FollowUserAction::execute()` memeriksa apakah relasi sudah ada.
3. Jika belum ada: INSERT ke `pagi_follows`, notifikasi dikirim ke pengguna yang diikuti.
4. Jika sudah ada: DELETE dari `pagi_follows`.

**Database:** Tabel `pagi_follows` (kolom: follower_id, following_id; unique constraint)

**Relasi data follow:** `GET /pagi/users/{user}/relations` — mengembalikan status follow dan jumlah follower/following

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Actions/FollowUserAction.php`
- Migration: `database/migrations/pagi/2026_06_07_000002_create_pagi_relations_tables.php`

---

### [C.14] Profil Publik

**Pengguna:** Semua role dan tamu yang memiliki URL
**Tujuan:** Menampilkan portofolio publik seorang pengguna — foto profil, bio, daftar karya, sertifikat, riwayat pendidikan.

**Route:**
- `GET /pagi/profile` — profil diri sendiri (auth required)
- `GET /pagi/profile/{user}` — profil pengguna lain berdasarkan ID
- `GET /pagi/{user:pagi_username}/{tab?}` — profil via username PAGI unik

**Frontend:**
- Direktori: `resources/js/pages/Modules/Pagi/User/Profile/`

**Proses:**
1. Sistem mengambil data profil dari cache `pagi_public_profile_{user_id}` atau query database.
2. Data yang ditampilkan: informasi dasar, daftar karya published, sertifikat, riwayat pendidikan.
3. Hanya karya dengan `is_published=true` dan `status=active` yang ditampilkan ke publik.

**Fitur dalam profil:**
- Pengurutan karya (`POST /pagi/profile/reorder-projects`) — drag & drop
- Pengecekan ketersediaan username (`GET /pagi/username/check`)

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Services/PagiProfileService.php`
- Route: `routes/pagi.php` baris 148–182, 353–370

---

### [C.15] Edit Profil

**Pengguna:** Mahasiswa
**Tujuan:** Mengubah informasi profil publik: foto avatar, nama tampilan, bio, username PAGI, dan informasi lainnya.

**Route:** `POST /pagi/profile/update` → `PagiDashboardController::updateProfile()`, throttle:uploads

**Input (dari `UpdateProfileRequest`):**
- `foto` (file: jpeg, jpg, png, webp, max:2048, nullable)
- `name` (string)
- `pagi_username` (string, unique)
- `bio`, `website`, `location` (string, nullable)
- Data lainnya sesuai field profil

**Validasi:**
- `foto`: image, mimes:jpeg,jpg,png,webp, max:2048
- `pagi_username`: unique di tabel users (kecuali diri sendiri)

**Pengaturan:** `GET/POST /pagi/settings` → `PagiDashboardController::settings()` dan `updateSettings()`

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Requests/UpdateProfileRequest.php`
- File: `app/Modules/Pagi/Requests/UpdateSettingsRequest.php`
- Route: `routes/pagi.php` baris 149–159

---

### [C.16] Manajemen Sertifikat

**Pengguna:** Mahasiswa
**Tujuan:** Mendokumentasikan sertifikat yang dimiliki mahasiswa lengkap dengan informasi penerbit, tanggal, credential ID, link, skill yang diperoleh, dan bukti media (gambar/PDF).

**Route:**
- `POST /pagi/certificates` → `PagiDashboardController::storeCertificate()`, throttle:uploads
- `PUT/POST /pagi/certificates/{id}` → `PagiDashboardController::updateCertificate()`, throttle:uploads
- `DELETE /pagi/certificates/{id}` → `PagiDashboardController::destroyCertificate()`
- `GET /pagi/certificates/org-logo` → mengambil logo organisasi
- `POST /pagi/certificates/org-logo` → mengunggah logo organisasi, throttle:uploads

**Proses (via `PagiCertificateService::store()`):**
1. Pengguna mengisi form sertifikat.
2. File media (gambar/PDF) diunggah.
3. Sistem melakukan scan konten file untuk deteksi injeksi (`<?php`, `<script`).
4. File PDF discan menggunakan `VirusScannerService` sebelum disimpan.
5. Gambar dikompres via `HandlesImageCompression::compressAndSaveImage()`.
6. Data sertifikat disimpan ke kolom JSON `metadata.certificates` pada model `User`.
7. Sertifikat secara otomatis disinkronasi ke CV aktif pengguna.

**Penyimpanan Media:**
- Gambar sertifikat: `storage/pagi/certificates/`
- Logo organisasi: `storage/pagi/org-logos/`

**Input (dari `StoreCertificateRequest`):**
- `title` (string, required)
- `issuer` (string, required)
- `date` (string, nullable)
- `expirationDate`, `credentialId`, `credentialUrl` (nullable)
- `skills` (JSON string → array)
- `newMedia` (file array: jpeg, png, gif, webp, pdf)

**Keamanan:**
- Deteksi skrip injeksi dalam konten file
- Scan antivirus ClamAV untuk PDF via `VirusScannerService`
- Validasi MIME type

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Services/PagiCertificateService.php`, method `store()` baris 20–80+
- File: `app/Modules/Pagi/Requests/StoreCertificateRequest.php`

---

### [C.17] Manajemen Riwayat Pendidikan

**Pengguna:** Mahasiswa
**Tujuan:** Mendokumentasikan riwayat pendidikan yang dapat dilihat di profil publik dan secara otomatis tersinkronasi ke CV.

**Route:**
- `POST /pagi/education` → `PagiDashboardController::storeEducation()`
- `PUT /pagi/education/{id}` → `PagiDashboardController::updateEducation()`
- `DELETE /pagi/education/{id}` → `PagiDashboardController::destroyEducation()`

**Input (dari `StoreEducationRequest`):**
- `institution` (string, required)
- `degree`, `field_of_study` (string, nullable)
- `start_year`, `end_year` (nullable)
- `description` (string, nullable)

**Data Tersimpan di:** Kolom JSON `metadata.education` pada model `User`

**Sinkronasi ke CV:** `PagiEducationService` secara otomatis memperbarui field `education` di semua CV (`pagi_cvs`) milik pengguna yang sama.

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Services/PagiEducationService.php`
- File: `app/Modules/Pagi/Requests/StoreEducationRequest.php`

---

### [C.18] Halaman Komunitas (People)

**Pengguna:** Semua role di modul PAGI
**Tujuan:** Halaman untuk menemukan dan terhubung dengan pengguna lain yang aktif di PAGI.

**Route:** `GET /pagi/people` → `PagiDashboardController::explorePeople()`

**Frontend:**
- File: `resources/js/pages/Modules/Pagi/User/People.vue` (26.3 KB)

**Proses:**
1. Sistem mengambil data dari cache `pagi_explore_people_{moduleId}` (TTL 60 detik).
2. Data mengambil pengguna aktif di modul PAGI dengan username yang terdaftar.
3. Jumlah followers dihitung via SQL aggregation (bukan PHP loop).
4. Karya terbaru per pengguna diambil via SQL subquery efisien.
5. Fitur "People You May Know" disediakan dari cache terpisah (TTL 600 detik).

**Pencarian Pengguna:**
- `GET /pagi/users/search?q={query}` → `PagiDashboardController::searchUsers()`
- Mencari berdasarkan `pagi_username`, `name`, atau `email` (LIKE query, max 10 hasil)
- Tidak menggunakan Meilisearch (live MySQL query agar data terbaru selalu tersedia)

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Services/PagiSocialService.php`, method `explorePeople()` baris 61–120+
- File: `app/Modules/Pagi/Services/PagiSocialService.php`, method `searchUsers()` baris 24–55

---

### [C.19] Pesan Langsung / Direct Message (Chat)

**Pengguna:** Semua role di modul PAGI
**Tujuan:** Komunikasi teks privat antar pengguna secara real-time dalam satu-ke-satu percakapan.

**Route:**
- `GET /pagi/messages` — daftar percakapan
- `GET /pagi/messages/contacts` — daftar kontak
- `GET /pagi/messages/{partner}` — riwayat percakapan dengan partner tertentu
- `POST /pagi/messages` — kirim pesan baru (throttle:pagi-chat-send)
- `POST /pagi/messages/read` — tandai pesan sebagai dibaca
- `POST /pagi/messages/pubkey` — update kunci publik E2EE
- `DELETE /pagi/messages/{message}` — hapus pesan
- `PATCH /pagi/messages/{message}` — edit pesan
- `POST /pagi/messages/{message}/react` — tambah reaksi emoji
- `DELETE /pagi/messages/clear-all/conversation` — hapus riwayat chat
- `POST /pagi/messages/pin` — pin percakapan
- `POST /pagi/messages/archive` — arsipkan percakapan
- `POST /pagi/messages/unread` — tandai sebagai belum dibaca
- `DELETE /pagi/messages/conversation/delete` — hapus percakapan
- `POST /pagi/messages/block` / `unblock` — blokir/buka blokir pengguna

**Frontend:**
- File: `resources/js/pages/Modules/Pagi/User/Messages.vue` (51.4 KB)

**Proses Pengiriman Pesan:**
1. Pengguna mengetik pesan di UI chat.
2. Sistem melakukan pre-scan konten via `ContentModerationService::scan()`.
3. Jika terdeteksi kata terlarang dan mode `reject`: pesan ditolak (422).
4. Jika mode `censor`: teks disensor sebelum dikirim.
5. Pesan disimpan ke `pagi_messages`.
6. Event `PagiMessageSent` didispatch → Laravel Reverb broadcast ke channel `pagi-messages.{receiver_id}`.
7. Penerima menerima pesan secara real-time melalui Laravel Echo tanpa reload halaman.

**Feature Gate:** Chat dapat dinonaktifkan admin via `portal_settings.pagi_enable_chat`. Jika disabled, seluruh endpoint chat menolak request.

**Input Pengiriman:**
- `receiver_id` (integer, required, exists:users,id, different dari sender)
- `parent_id` (integer, nullable, exists:pagi_messages,id — untuk reply)
- `body` (string, required, max:4000)

**Database:** Tabel `pagi_messages` (conversation_id, sender_id, receiver_id, body, read_at)

**Broadcast Events:**
| Event | Kondisi |
|---|---|
| `PagiMessageSent` | Pesan baru dikirim |
| `PagiMessagesRead` | Pesan ditandai dibaca |
| `PagiMessageDeleted` | Pesan dihapus |
| `PagiMessageEdited` | Pesan diedit |
| `PagiMessageReacted` | Reaksi emoji ditambahkan |
| `PagiUnreadCountUpdated` | Jumlah unread berubah |

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/PagiChatController.php`, method `store()` baris 83–130
- File: `app/Events/PagiMessageSent.php`
- File: `app/Modules/Pagi/Services/PagiChatService.php`
- Migration: `database/migrations/pagi/2026_05_29_072924_create_pagi_messages_table.php`

---

### [C.20] Notifikasi

**Pengguna:** Semua role
**Tujuan:** Pusat notifikasi yang merekap seluruh aktivitas yang berkaitan dengan pengguna (like, komentar, follow, kolaborasi, peringatan, dll).

**Route:**
- `GET /pagi/notifications` → `PagiDashboardController::notifications()`
- `POST /pagi/notifications/mark-all-read` → tandai semua sebagai dibaca
- `POST /pagi/notifications/{id}/mark-read` → tandai satu notifikasi
- `DELETE /pagi/notifications/clear-all` → hapus semua notifikasi
- `DELETE /pagi/notifications/{id}` → hapus satu notifikasi

**Frontend:**
- File: `resources/js/pages/Modules/Pagi/User/Notifications.vue` (15.5 KB)

**Jenis Notifikasi (teridentifikasi dari source code):**
- `like` — seseorang menyukai karya
- `comment` — seseorang mengomentari karya
- `follow` — seseorang mengikuti
- `collaboration` / `collaboration_invite` — undangan kolaborasi
- `collaboration_accepted` — kolaborator menerima undangan
- `new_work` — pengguna yang diikuti memublikasikan karya baru
- Notifikasi peringatan dari admin

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Services/PagiNotificationService.php`
- File: `app/Notifications/PagiNotification.php`
- Route: `routes/pagi.php` baris 111–121

---

### [C.21] Laporan Pelanggaran (Report Work)

**Pengguna:** Semua role (user yang melihat karya)
**Tujuan:** Melaporkan karya yang dianggap melanggar ketentuan kepada admin untuk ditinjau.

**Route:** `POST /pagi/works/report` → `AdminModerationController::storeReport()`

**Input:**
- `work_id` (integer)
- `reason` (enum: inappropriate_content, copyright_violation, spam, harassment, misinformation, other)
- `description` (text, nullable)

**Output:**
- Record baru di `pagi_reports` dengan status `pending`

**Database:** Tabel `pagi_reports` (work_id, reporter_id, reason, description, status, reviewed_by, admin_note, reviewed_at)

**Bukti Implementasi:**
- Route: `routes/pagi.php` baris 221–222
- File: `app/Modules/Pagi/Controllers/AdminModerationController.php`, method `storeReport()`
- Migration: `database/migrations/pagi/2026_05_19_145114_create_pagi_reports_table.php`

---

### [C.22] CV Builder

**Pengguna:** Mahasiswa dan Alumni
**Tujuan:** Membuat dan mengelola CV digital berbasis template yang dapat diunduh dalam format PDF.

**Route:**
- `GET /pagi/cv` → daftar CV milik pengguna
- `GET /pagi/cv/templates` → galeri template
- `GET /pagi/cv/profile-data` → ambil data profil untuk sinkronasi ke CV
- `POST /pagi/cv` → buat CV baru
- `GET /pagi/cv/{cv}/edit` → halaman editor CV
- `PUT /pagi/cv/{cv}` → simpan perubahan CV
- `POST /pagi/cv/{cv}/duplicate` → duplikasi CV
- `DELETE /pagi/cv/{cv}` → hapus CV
- `POST /pagi/cv/{cv}/upload-photo` → unggah foto khusus CV, throttle:uploads
- `GET /pagi/cv/{cv}/download` → unduh PDF
- `GET /pagi/cv/{cv}/shared` → pratinjau/share CV (auth required, SEC-004)

**Frontend:**
- Daftar CV: `resources/js/pages/Modules/Pagi/User/Cv/CvDashboard.vue` (27.2 KB)
- Builder: `resources/js/pages/Modules/Pagi/User/Cv/CvBuilder.vue` (87.3 KB)
- Preview: `resources/js/pages/Modules/Pagi/User/Cv/CvPreview.vue` (52.1 KB)
- Galeri template: `resources/js/pages/Modules/Pagi/User/Cv/components/TemplateGallery.vue` (17.7 KB)
- Kustomisasi tema: `resources/js/pages/Modules/Pagi/User/Cv/components/ThemeCustomizer.vue` (13.3 KB)

**Template yang tersedia (terverifikasi dari validasi):**
`ats-professional`, `modern-sidebar`, `executive`, `creative-minimal`, `student-resume`, `custom`

**Bagian konten CV (terverifikasi dari `PagiCvController::update()` dan skema `pagi_cvs`):**
- `personal_info` — nama, email, telepon, alamat, profil singkat
- `education` — riwayat pendidikan (sinkronasi dari profil)
- `experience` — pengalaman kerja
- `organizations` — pengalaman organisasi
- `skills` — keahlian
- `certifications` — sertifikat (sinkronasi dari profil)
- `trainings` — pelatihan yang diikuti
- `achievements` — prestasi
- `languages` — bahasa yang dikuasai
- `references` — referensi
- `customization` — kustomisasi tampilan (warna, font, ukuran, urutan section)

**Pembatasan:**
- Maksimal 3 CV per pengguna (divalidasi di `PagiCvService::createCv()` dan `PagiCvController::templates()`)

**Sinkronasi data:**
- `GET /pagi/cv/profile-data` → `PagiCvService::mapUserProfileToCvData()` — otomatis mengisi data dari profil pengguna

**Status CV:** `draft` (belum lengkap) atau `published` (diisi dari `determineCvStatus()`)

**Unduh PDF:**
- Menggunakan `barryvdh/laravel-dompdf`
- View template: `pdf.cv`
- Kertas A4 portrait

**Share CV:**
- Route: `GET /pagi/cv/{cv}/shared` — memerlukan autentikasi
- Hanya CV berstatus `published` yang dapat dilihat selain pemilik
- Keamanan: komentar kode `SEC-004` mencatat alasan — mencegah enumeration IDOR tanpa login

**Input foto CV:**
- `photo`: image, mimes:jpeg,png,jpg,gif,webp, max:2048 KB

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/PagiCvController.php` (seluruh file, 289 baris)
- File: `app/Modules/Pagi/Services/PagiCvService.php`
- Migration: `database/migrations/pagi/2026_06_06_145114_create_pagi_cvs_table.php`

---

### [C.23] Dashboard Admin

**Pengguna:** Admin (prodi, admin-akademik, admin-universitas, admin, super-admin)
**Tujuan:** Halaman ringkasan kondisi modul PAGI — jumlah karya, pengguna, laporan, statistik populer.

**Route:** `GET /pagi/admin/` → `AdminDashboardController::index()`

**Frontend:** `resources/js/pages/Modules/Pagi/Admin/Dashboard.vue` (24.3 KB)

**Data yang ditampilkan:**
- Statistik agregat: total karya, pengguna aktif, laporan pending, peringatan aktif
- Karya populer (diurutkan berdasarkan `views_count` DESC)
- Aktivitas terbaru (3 karya terbaru, 2 laporan, 2 peringatan)
- Ringkasan moderasi (jumlah per status)
- Grafik data 7 hari (`buildChartData('7d')`)

**Polling API (JSON endpoints):**
- `GET /pagi/admin/api/stats` — polling statistik real-time
- `GET /pagi/admin/api/chart` — data grafik
- `GET /pagi/admin/api/notifications` — notifikasi admin

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/AdminDashboardController.php`, method `index()` baris 42–92

---

### [C.24] Analitik Admin

**Pengguna:** Admin
**Tujuan:** Halaman analitik mendalam — statistik penggunaan, pertumbuhan, distribusi kategori, peta aktivitas.

**Route:** `GET /pagi/admin/analytics` → `AdminDashboardController::analytics()`

**Frontend:** Direktori `resources/js/pages/Modules/Pagi/Admin/Analytics/`

**Polling API:**
- `GET /pagi/admin/api/analytics-stats`
- `GET /pagi/admin/api/analytics-charts`

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/AdminDashboardController.php`, method `analytics()` baris 97+

---

### [C.25] Moderasi Konten oleh Admin

**Pengguna:** Admin
**Tujuan:** Mengelola laporan masuk, meninjau karya bermasalah, memberikan sanksi, dan memulihkan konten.

**Route Moderasi:**
- `GET /pagi/admin/reports` — daftar laporan
- `GET /pagi/admin/reports/archive` — arsip laporan
- `GET /pagi/admin/warnings` — daftar peringatan
- `GET /pagi/admin/takedowns` — daftar karya yang di-takedown
- `POST /pagi/admin/content/work/{work}/moderate` — sembunyikan konten
- `POST /pagi/admin/takedowns/{work}/restore` — pulihkan konten

**Frontend:**
- Laporan: Direktori `resources/js/pages/Modules/Pagi/Admin/Reports/`

**Proses Moderasi:**
1. Laporan masuk melalui user report atau AI auto-moderation.
2. Admin membuka halaman Reports.
3. Admin meninjau karya yang dilaporkan.
4. Admin memilih tindakan: sembunyikan karya (status → `hidden`) atau tindakan lain.
5. Cache galeri dihapus.
6. Notifikasi dikirim ke pemilik karya.

**Tindakan yang tersedia:**
- Sembunyikan konten (`POST /pagi/admin/content/work/{work}/moderate`)
- Pulihkan konten (`POST /pagi/admin/takedowns/{work}/restore`)
- Kirim peringatan ke pengguna (`POST /pagi/admin/users/{user}/warn`)
- Cabut peringatan (`POST /pagi/admin/warnings/{warning}/revoke`)
- Update status pengguna (`POST /pagi/admin/users/{user}/status`)
- Kirim notifikasi ke pengguna (`POST /pagi/admin/users/{user}/notify`)
- Reset semua data moderasi (`POST /pagi/admin/reset-moderation`)
- Hapus semua karya (Danger Zone: `DELETE /pagi/admin/reset-all-works` — dilindungi verifikasi password + text konfirmasi)

**Banding Pengguna:**
- `POST /pagi/admin/content/work/{work}/appeal` — pengguna mengajukan banding
- `POST /pagi/admin/appeals/{report}/approve` — admin menyetujui banding
- `POST /pagi/admin/appeals/{report}/reject` — admin menolak banding

**Status laporan (dari migration):** `pending`, `reviewed`, `dismissed`, `actioned`

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/AdminModerationController.php` (1046 baris)
- File: `app/Models/Pagi/PagiReport.php`
- File: `app/Models/Pagi/PagiWarning.php`

---

### [C.26] Peringatan (Warning) ke Pengguna

**Pengguna:** Admin
**Tujuan:** Mengirimkan peringatan formal ke mahasiswa yang melanggar ketentuan.

**Route:** `POST /pagi/admin/users/{user}/warn` → `AdminUserController::warnUser()`

**Input:**
- `reason` (text, required)
- `type` (enum: inappropriate_content, copyright, spam, harassment, repeat_violation, other)
- `severity` (enum: low, medium, high)
- `work_id` (nullable, integer)
- `expires_at` (timestamp, nullable)

**Output:**
- Record baru di `pagi_warnings`
- Notifikasi dikirim ke pengguna yang diperingatkan

**Status warning:** `is_active` (boolean), `expires_at` (timestamp)

**Bukti Implementasi:**
- Migration: `database/migrations/pagi/2026_05_19_145114_create_pagi_reports_table.php` (pagi_warnings schema)
- File: `app/Modules/Pagi/Controllers/AdminUserController.php`

---

### [C.27] Manajemen Showcase / Pameran

**Pengguna:** Admin
**Tujuan:** Memilih dan menampilkan karya-karya pilihan dalam halaman showcase/pameran resmi modul PAGI.

**Route:**
- `GET /pagi/admin/showcase` → daftar showcase
- `POST /pagi/admin/showcase` → update pengaturan showcase
- `POST /pagi/admin/showcase/toggle/{work}` → tambah/hapus karya dari showcase
- `POST /pagi/admin/showcase/request-completeness/{work}` → minta kelengkapan karya sebelum ditampilkan

**Frontend:** Direktori `resources/js/pages/Modules/Pagi/Admin/Showcase/`

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/AdminShowcaseController.php`
- Route: `routes/pagi.php` baris 279–286

---

### [C.28] Kamus Kata Terlarang (Text Dictionary)

**Pengguna:** Admin
**Tujuan:** Mengelola daftar kata terlarang kustom yang digunakan oleh sistem moderasi teks.

**Route:** `GET /pagi/admin/text-dictionary` → `AdminModerationController::textDictionary()`

**Frontend:** Direktori `resources/js/pages/Modules/Pagi/Admin/TextDictionary/`

**Proses:**
- Admin menambahkan atau menghapus kata dari kamus kustom.
- Data disimpan sebagai JSON array di `portal_settings` dengan key `pagi_banned_words`.
- `ContentModerationService::getBannedWords()` menggabungkan kamus bawaan + kamus kustom admin.

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Services/ContentModerationService.php`, method `getBannedWords()` baris 79–105
- Route: `routes/pagi.php` baris 255–256

---

### [C.29] Konfigurasi Moderasi Gambar (Image Dictionary)

**Pengguna:** Admin
**Tujuan:** Mengatur pengaturan moderasi gambar — mengaktifkan/menonaktifkan mesin heuristik lokal, mengaktifkan Google Gemini Vision AI, mengatur threshold.

**Route:** `GET /pagi/admin/image-dictionary` → `AdminModerationController::imageDictionary()`

**Frontend:** Direktori `resources/js/pages/Modules/Pagi/Admin/ImageDictionary/`

**Pengaturan yang dikelola (tersimpan di `portal_settings`):**
- `pagi_enable_vision_ai` — aktifkan Google Gemini Vision
- `pagi_enable_google_ai` — aktifkan Google AI secara umum
- `pagi_enable_local_engine` — aktifkan mesin heuristik lokal
- `pagi_google_ai_api_key` — API key Gemini
- `pagi_google_ai_model` — model Gemini yang digunakan

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Services/ContentModerationService.php`, method `scan()` baris 110–176
- Route: `routes/pagi.php` baris 257–258

---

### [C.30] Pengujian Koneksi Google Gemini AI

**Pengguna:** Admin
**Tujuan:** Memverifikasi bahwa API key Google Gemini yang dikonfigurasi dapat terhubung dengan benar.

**Route:** `POST /pagi/admin/test-google-ai` → `AdminModerationController::testGoogleAiApi()`

**Route Tambahan:** `POST /pagi/admin/settings/fetch-google-ai-models` → mengambil daftar model Gemini yang tersedia

**Bukti Implementasi:**
- Route: `routes/pagi.php` baris 273–274, 312–313

---

### [C.31] Pengaturan Umum Modul Admin

**Pengguna:** Admin
**Tujuan:** Mengatur konfigurasi umum modul PAGI dari antarmuka admin.

**Route:**
- `GET /pagi/admin/settings` → halaman pengaturan
- `POST /pagi/admin/settings` → simpan pengaturan

**Frontend:** Direktori `resources/js/pages/Modules/Pagi/Admin/Settings/`

**Bukti Implementasi:**
- Route: `routes/pagi.php` baris 269–272

---

### [C.32] Manajemen Pengguna oleh Admin

**Pengguna:** Admin
**Tujuan:** Melihat dan mengelola daftar pengguna yang terdaftar di modul PAGI.

**Route:**
- `GET /pagi/admin/users` → `AdminUserController::index()`
- `POST /pagi/admin/users/{user}/status` → `AdminUserController::updateUserStatus()`
- `POST /pagi/admin/users/{user}/notify` → `AdminUserController::sendNotificationToUser()`

**Frontend:** Direktori `resources/js/pages/Modules/Pagi/Admin/Users/`

**Pencarian:** `GET /pagi/admin/api/instant-search` → `AdminUserController::instantSearch()`

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Controllers/AdminUserController.php`
- Route: `routes/pagi.php` baris 263–264, 289–301

---

### [C.33] Moderasi Konten Otomatis (AI Auto-Moderation)

**Pengguna:** Sistem (dipicu otomatis saat karya dibuat/diedit)
**Tujuan:** Mendeteksi dan menandai konten yang melanggar ketentuan secara otomatis tanpa intervensi admin, menggunakan kombinasi kamus lokal, heuristik, dan Google Gemini AI.

**Dipicu oleh:** `PagiEditorController::checkAutoModeration()` yang dipanggil di `store()` dan `quickStore()`

**Mesin Moderasi (5 lapisan, dari `ContentModerationService`):**

**Layer 1 — Local Banned Words Dictionary:**
- Kamus bawaan dengan 6 kategori: `judi_online`, `profanity`, `harassment`, `sexual`, `threat`, `phishing`
- Deteksi leetspeak: konversi `0→o`, `1→i`, `4→a`, `@→a`, `$→s`, dll.
- Deteksi pemisah tersembunyi: `a.n.j.i.n.g → anjing`
- Kamus kustom admin dari `portal_settings.pagi_banned_words`
- Implementasi: `ContentModerationService::scan()`, `normalizeText()`, `getBannedWords()`

**Layer 2 — Heuristik Konteks AI (fallback jika Google AI diaktifkan tapi API key kosong):**
- Regex pattern untuk pola terselubung
- Dipicu dari `evaluateAiContextHeuristics()`

**Layer 3 — Google Gemini AI (teks, opsional):**
- REST API ke `generativelanguage.googleapis.com/v1beta/models/{model}:generateContent`
- System prompt: moderasi konten kampus
- Timeout: 4 detik
- Fallback ke heuristik lokal jika gagal
- Implementasi: `ContentModerationService::evaluateWithGoogleGeminiApi()`

**Layer 4 — Heuristik Lokal Gambar (pixel analysis, 0 API token):**
- Resize ke 100×100 pixel menggunakan PHP GD
- Rasio skin tone > 0.48 → NSFW (sexual)
- Rasio warna merah darah > 0.12 → Violence (threat)
- Implementasi: `ContentModerationService::evaluateImageLocalHeuristics()`

**Layer 5 — Google Gemini Vision API (gambar, opsional):**
- Kirim gambar sebagai Base64 + deskripsi caption
- Analisis multimodal
- Timeout: 30 detik
- Implementasi: `ContentModerationService::evaluateImageWithGoogleGeminiApi()`

**Hasil moderasi:**
```
is_flagged: true/false
severity: clean / medium / high / critical
categories: [array kategori yang terdeteksi]
matched_words: [kata/frasa yang terdeteksi]
censored_text: teks tersensor (a***ng)
```

**Bila terdeteksi:**
- Status karya diubah ke `review`, `is_published` diubah ke `false`
- Laporan otomatis dibuat di `pagi_reports` dengan reporter = pemilik karya dan prefix `[Auto Moderasi AI]`

**Bukti Implementasi:**
- File: `app/Modules/Pagi/Services/ContentModerationService.php` (seluruh file, 592 baris)
- File: `app/Modules/Pagi/Controllers/PagiEditorController.php`, method `checkAutoModeration()` baris 796–830

---

## D. ALUR SISTEM

### Alur 1: Publikasi Karya

```
Mahasiswa buka /pagi/editor
    ↓
Mahasiswa susun konten (Tiptap Block Editor)
    ↓
Mahasiswa klik "Publish" → POST /pagi/editor
    ↓
StorePortfolioRequest::rules() — validasi input
    ↓
PortfolioService::processContentBlocks() — proses & simpan file dalam blok
    ↓
PortfolioService::saveCoverImage() — kompresi & simpan cover
    ↓
PagiWork::create() — simpan ke pagi_works
    ↓
PortfolioService::processAndNotifyCollaborators() — notifikasi kolaborator
    ↓
ContentModerationService::scan() — scan teks (judul + deskripsi)
    ↓
ContentModerationService::scanImage() — scan cover (gambar)
    ↓
[Aman] → is_published=true, status=active
PortfolioService::notifyFollowers() → notifikasi ke followers
PagiWorkCreated::dispatch() → event broadcast
Redirect ke dashboard dengan pesan sukses
    ↓
[Terdeteksi Pelanggaran] → is_published=false, status=review
PagiReport::create() → laporan otomatis ke admin
Redirect ke dashboard dengan pesan peringatan
```

**Bukti:** `PagiEditorController::store()` baris 128–186

---

### Alur 2: Interaksi Karya (Like, Komentar, Reply)

```
Pengguna buka galeri /pagi/gallery
    ↓
Klik karya → fetch GET /pagi/preview/{id}/data (throttle:60/menit)
    ↓
Modal terbuka: tampil konten blok + komentar + likes
    ↓
[Like Karya]
POST /pagi/preview/{id}/like → LikeWorkAction::execute()
    ↓
PagiWork::likesRelation()->toggle(user_id) → INSERT/DELETE pagi_work_likes
    ↓
Return data likes terbaru → update UI
    ↓
[Komentar]
POST /pagi/preview/{id}/comment (throttle:20/menit)
    ↓
ContentModerationService::scan() → cek komentar
    ↓
[Aman] CreateCommentAction::execute() → INSERT pagi_work_comments (parent_id=null)
    ↓
Return komentar terbaru → update UI
    ↓
[Reply]
POST /pagi/preview/{id}/comment/{comment}/reply (throttle:20/menit)
    ↓
ReplyCommentAction::execute() → INSERT pagi_work_comments (parent_id={comment_id})
    ↓
Return komentar terbaru → update UI
```

---

### Alur 3: Chat Real-Time

```
Pengguna A buka /pagi/messages
    ↓
PagiChatService::getConversations() → daftar percakapan
    ↓
Pengguna A pilih percakapan → GET /pagi/messages/{partner}
    ↓
PagiChatService::getConversationPayload() → riwayat pesan (pagination cursor)
    ↓
Pengguna A ketik pesan → POST /pagi/messages (throttle:pagi-chat-send)
    ↓
ContentModerationService::scan(body) → cek konten
    ↓
[Ditolak] → response 422 (jika mode reject)
[Disensor] → body disensor, lanjut kirim
[Aman] PagiChatService::sendMessage() → INSERT pagi_messages
    ↓
PagiMessageSent::dispatch($message) → broadcast ke channel pagi-messages.{receiver_id}
    ↓
Laravel Reverb → WebSocket push ke subscriber
    ↓
Pengguna B [Laravel Echo listener] → terima event PagiMessageSent
    ↓
UI Pengguna B diperbarui real-time tanpa reload halaman
```

---

### Alur 4: Moderasi Karya

```
[Sumber 1: User Report]
Pengguna A melihat karya yang melanggar → POST /pagi/works/report
    ↓
INSERT pagi_reports (status: pending)

[Sumber 2: AI Auto-Moderation]
Karya dibuat → ContentModerationService::scan() + scanImage()
    ↓
Terdeteksi pelanggaran → INSERT pagi_reports ([Auto Moderasi AI])
    ↓
Status karya → review, is_published → false
    ↓
Admin buka /pagi/admin/reports → tampil daftar laporan pending
    ↓
Admin tinjau laporan, buka karya
    ↓
[Tindakan Admin]
POST /pagi/admin/content/work/{id}/moderate → UPDATE pagi_works SET status='hidden'
    ↓
Cache::forget('pagi_feed_projects_raw') → galeri publik diperbarui
    ↓
PagiNotification → notifikasi dikirim ke pemilik karya
    ↓
Admin dapat kirim peringatan → POST /pagi/admin/users/{user}/warn → INSERT pagi_warnings
```

---

### Alur 5: CV Builder

```
Mahasiswa buka /pagi/cv
    ↓
Pilih "Buat CV Baru" → GET /pagi/cv/templates (cek limit 3 CV)
    ↓
Pilih template → POST /pagi/cv (template_id, title)
    ↓
PagiCvService::createCv() → data profil otomatis disinkronasi → INSERT pagi_cvs
    ↓
Redirect ke /pagi/cv/{id}/edit → halaman CvBuilder.vue
    ↓
Pengguna isi/edit setiap section (personal info, pendidikan, pengalaman, dll.)
    ↓
PUT /pagi/cv/{id} → PagiCvService::updateCv()
    ↓
Nama & email dipaksa dari data auth user (keamanan)
    ↓
Status CV dihitung otomatis (draft / published)
    ↓
UPDATE pagi_cvs
    ↓
Pengguna klik "Unduh PDF" → GET /pagi/cv/{id}/download
    ↓
Pdf::loadView('pdf.cv', [...]) → DomPDF → A4 portrait PDF → download
```

---

### Alur 6: Profil Publik

```
Pengguna lain akses /pagi/{username}/works
    ↓
PagiDashboardController::userWorks() / publicProfile()
    ↓
PagiProfileService → ambil data profil + karya dari cache/DB
    ↓
Hanya karya is_published=true, status=active yang ditampilkan
    ↓
Karya visible berdasarkan visibility=Everyone atau null
    ↓
Tampil profil: foto, bio, daftar karya, sertifikat, pendidikan
    ↓
Pengguna menekan "Follow" → POST /pagi/users/{user}/follow
    ↓
FollowUserAction::execute() → INSERT/DELETE pagi_follows
    ↓
Notifikasi dikirim ke pengguna yang diikuti
```

---

## E. HAK AKSES PENGGUNA

| Pengguna | Hak Akses |
|---|---|
| **Mahasiswa** | Dashboard, Editor (buat/edit/hapus karya), Galeri, People, Profil (edit profil, sertifikat, pendidikan), CV Builder, Chat, Notifikasi, Like, Komentar, Follow, Laporan karya orang lain |
| **Alumni** | CV Builder, Galeri, Profil, Notifikasi, Chat |
| **Dosen/Staff** | Galeri, People, Profil (view), Notifikasi, Like, Komentar (terbatas) |
| **prodi, admin-akademik, admin-universitas, admin, super-admin** | Seluruh akses user + Dashboard admin, Analitik, Moderasi, Manajemen pengguna, Showcase, Pengaturan kamus, Pengaturan sistem |

---

## F. DAFTAR FILE PENTING

### Route

| File | Fungsi |
|---|---|
| `routes/pagi.php` | Seluruh definisi route modul PAGI (371 baris, 70+ route) |

### Controller

| File | Fungsi |
|---|---|
| `app/Modules/Pagi/Controllers/PagiDashboardController.php` | Dashboard, galeri, profil, interaksi sosial, notifikasi |
| `app/Modules/Pagi/Controllers/PagiEditorController.php` | Editor portofolio, kolaborasi |
| `app/Modules/Pagi/Controllers/PagiChatController.php` | Direct message, pin, archive, blokir |
| `app/Modules/Pagi/Controllers/PagiCvController.php` | CV Builder, upload foto, PDF |
| `app/Modules/Pagi/Controllers/AdminDashboardController.php` | Dashboard statistik admin, analitik |
| `app/Modules/Pagi/Controllers/AdminModerationController.php` | Moderasi, laporan, kamus, pengaturan AI |
| `app/Modules/Pagi/Controllers/AdminUserController.php` | Manajemen pengguna, peringatan |
| `app/Modules/Pagi/Controllers/AdminShowcaseController.php` | Manajemen showcase/pameran |
| `app/Modules/Pagi/Controllers/AdminWorkController.php` | Daftar semua karya (admin view) |

### Service

| File | Fungsi |
|---|---|
| `app/Modules/Pagi/Services/PortfolioService.php` | Proses konten blok, simpan cover, kolaborator, notifikasi followers |
| `app/Modules/Pagi/Services/PagiSocialService.php` | Galeri eksplorasi, pencarian pengguna, data People |
| `app/Modules/Pagi/Services/PagiChatService.php` | Logika chat, percakapan, unread count |
| `app/Modules/Pagi/Services/PagiNotificationService.php` | Format dan filter notifikasi |
| `app/Modules/Pagi/Services/PagiProfileService.php` | Data profil publik |
| `app/Modules/Pagi/Services/PagiCertificateService.php` | CRUD sertifikat, upload media, sinkronasi CV |
| `app/Modules/Pagi/Services/PagiEducationService.php` | CRUD pendidikan, sinkronasi CV |
| `app/Modules/Pagi/Services/PagiCvService.php` | Buat, update, duplikasi, ekspor CV |
| `app/Modules/Pagi/Services/ContentModerationService.php` | Seluruh mesin moderasi teks dan gambar |

### Action

| File | Fungsi |
|---|---|
| `app/Modules/Pagi/Actions/LikeWorkAction.php` | Toggle like pada karya |
| `app/Modules/Pagi/Actions/LikeCommentAction.php` | Toggle like pada komentar |
| `app/Modules/Pagi/Actions/LikeReplyAction.php` | Toggle like pada balasan |
| `app/Modules/Pagi/Actions/CreateCommentAction.php` | Buat komentar baru |
| `app/Modules/Pagi/Actions/ReplyCommentAction.php` | Buat balasan komentar |
| `app/Modules/Pagi/Actions/FollowUserAction.php` | Toggle follow pengguna |

### Model

| File | Tabel | Fungsi |
|---|---|---|
| `app/Models/Pagi/PagiWork.php` | `pagi_works` | Model karya utama, searchable (Scout), cache invalidation booted event |
| `app/Models/Pagi/PagiWorkComment.php` | `pagi_work_comments` | Model komentar dengan nested reply |
| `app/Models/Pagi/PagiWorkLike.php` | `pagi_work_likes` | Model like karya |
| `app/Models/Pagi/PagiMessage.php` | `pagi_messages` | Model pesan chat |
| `app/Models/Pagi/PagiReport.php` | `pagi_reports` | Model laporan pelanggaran |
| `app/Models/Pagi/PagiWarning.php` | `pagi_warnings` | Model peringatan admin |
| `app/Models/Pagi/PagiFollow.php` | `pagi_follows` | Model relasi follow |
| `app/Models/Pagi/PagiBlock.php` | `pagi_blocks` | Model relasi blokir pengguna |
| `app/Models/Pagi/PagiCv.php` | `pagi_cvs` | Model dokumen CV |
| `app/Models/Pagi/PagiTag.php` | `pagi_tags` | Model tag karya |
| `app/Models/Pagi/PagiCustomWork.php` | `pagi_custom_works` | Model karya kustom |
| `app/Models/Pagi/PagiActiveChat.php` | `pagi_active_chats` | Status chat aktif |
| `app/Models/Pagi/PagiArchivedChat.php` | `pagi_archived_chats` | Status chat diarsipkan |
| `app/Models/Pagi/PagiPinnedChat.php` | `pagi_pinned_chats` | Status chat di-pin |
| `app/Models/Pagi/PagiUnreadChat.php` | `pagi_unread_chats` | Status chat belum dibaca |
| `app/Models/Pagi/PagiClearedChat.php` | `pagi_cleared_chats` | Riwayat clear chat |

### Migration

| File | Tabel yang Dibuat/Dimodifikasi |
|---|---|
| `2026_04_22_174256_create_pagi_portfolios_table.php` | `pagi_portfolios` (cikal bakal pagi_works) |
| `2026_05_19_145114_create_pagi_reports_table.php` | `pagi_reports`, `pagi_warnings`, `pagi_tags`, `pagi_portfolio_tags` |
| `2026_05_29_072924_create_pagi_messages_table.php` | `pagi_messages` |
| `2026_05_29_170000_add_reactions_to_pagi_messages_table.php` | Tambah kolom `reactions` (JSON) |
| `2026_05_31_000000_create_pagi_messages_archive_table.php` | `pagi_messages_archive` |
| `2026_05_31_100000_add_deleted_for_to_pagi_messages.php` | Tambah kolom `deleted_at` |
| `2026_05_31_110000_add_edit_delete_to_pagi_messages.php` | Tambah kolom `is_edited` |
| `2026_06_02_191358_add_tools_used_to_pagi_portfolios_table.php` | Tambah `tools_used` |
| `2026_06_03_000001_add_pagi_username_to_users_table.php` | Tambah `pagi_username` ke `users` |
| `2026_06_05_120135_create_pagi_custom_portfolios_table.php` | `pagi_custom_portfolios` |
| `2026_06_06_065839_add_visibility_to_pagi_portfolios_table.php` | Tambah `visibility` |
| `2026_06_06_070847_rename_pagi_portfolios_to_pagi_works.php` | Rename ke `pagi_works`, `pagi_work_tags` |
| `2026_06_06_145114_create_pagi_cvs_table.php` | `pagi_cvs` |
| `2026_06_06_150000_add_review_to_pagi_works_status_enum.php` | Tambah status `review` ke ENUM |
| `2026_06_07_000002_create_pagi_relations_tables.php` | `pagi_follows`, `pagi_blocks`, `pagi_pinned_chats`, `pagi_archived_chats`, `pagi_active_chats`, `pagi_unread_chats`, `pagi_cleared_chats` |
| `2026_06_15_000000_create_pagi_work_normalized_tables.php` | `pagi_work_likes`, `pagi_work_comments`, `pagi_comment_likes` |
| `2026_06_17_000001_add_performance_indexes_to_pagi_tables.php` | Index performa pada tabel utama |

### Form Request

| File | Fungsi |
|---|---|
| `app/Modules/Pagi/Requests/StorePortfolioRequest.php` | Validasi pembuatan karya baru |
| `app/Modules/Pagi/Requests/UpdatePortfolioRequest.php` | Validasi pengeditan karya |
| `app/Modules/Pagi/Requests/QuickStorePortfolioRequest.php` | Validasi pembuatan karya cepat |
| `app/Modules/Pagi/Requests/QuickUpdatePortfolioRequest.php` | Validasi edit karya cepat |
| `app/Modules/Pagi/Requests/StoreGalleryItemRequest.php` | Validasi item galeri |
| `app/Modules/Pagi/Requests/StoreCertificateRequest.php` | Validasi sertifikat baru |
| `app/Modules/Pagi/Requests/UpdateCertificateRequest.php` | Validasi update sertifikat |
| `app/Modules/Pagi/Requests/StoreEducationRequest.php` | Validasi pendidikan baru |
| `app/Modules/Pagi/Requests/UpdateEducationRequest.php` | Validasi update pendidikan |
| `app/Modules/Pagi/Requests/UpdateProfileRequest.php` | Validasi update profil |
| `app/Modules/Pagi/Requests/UpdateSettingsRequest.php` | Validasi pengaturan pengguna |

### Frontend

| File | Fungsi |
|---|---|
| `resources/js/pages/Modules/Pagi/User/MahasiswaDashboard.vue` | Dashboard utama mahasiswa |
| `resources/js/pages/Modules/Pagi/User/Gallery.vue` | Galeri eksplorasi karya |
| `resources/js/pages/Modules/Pagi/User/Messages.vue` | Chat direct message |
| `resources/js/pages/Modules/Pagi/User/People.vue` | Halaman komunitas |
| `resources/js/pages/Modules/Pagi/User/Notifications.vue` | Pusat notifikasi |
| `resources/js/pages/Modules/Pagi/User/Editor/Editor.vue` | Halaman editor karya |
| `resources/js/pages/Modules/Pagi/User/Editor/EditorCanvas.vue` | Canvas block editor |
| `resources/js/pages/Modules/Pagi/User/Editor/EditorPublishModal.vue` | Modal publikasi |
| `resources/js/pages/Modules/Pagi/User/Editor/PagiTiptapEditor.vue` | Wrapper Tiptap |
| `resources/js/pages/Modules/Pagi/User/Cv/CvDashboard.vue` | Daftar CV |
| `resources/js/pages/Modules/Pagi/User/Cv/CvBuilder.vue` | Builder CV |
| `resources/js/pages/Modules/Pagi/User/Cv/CvPreview.vue` | Preview CV |
| `resources/js/pages/Modules/Pagi/Admin/Dashboard.vue` | Dashboard admin |

### Composable

| File | Fungsi |
|---|---|
| `resources/js/pages/Modules/Pagi/User/Editor/useEditorFileUpload.ts` | Upload file dalam editor |
| `resources/js/pages/Modules/Pagi/User/Editor/useEditorDraft.ts` | Manajemen draft otomatis |
| `resources/js/pages/Modules/Pagi/User/Editor/useEditorCanvas.ts` | Logika canvas editor |
| `resources/js/pages/Modules/Pagi/User/Editor/useEditorTags.ts` | Manajemen tag |
| `resources/js/pages/Modules/Pagi/User/Editor/useEditorCollaborators.ts` | Manajemen kolaborator |
| `resources/js/pages/Modules/Pagi/User/Cv/useCvActions.ts` | Aksi CV (simpan, duplikasi) |
| `resources/js/pages/Modules/Pagi/User/Cv/useCvHistory.ts` | Riwayat perubahan CV |

### Event

| File | Fungsi | Channel |
|---|---|---|
| `app/Events/PagiMessageSent.php` | Pesan baru dikirim | `pagi-messages.{user_id}` |
| `app/Events/PagiMessagesRead.php` | Pesan ditandai dibaca | `pagi-messages.{user_id}` |
| `app/Events/PagiMessageDeleted.php` | Pesan dihapus | `pagi-messages.{user_id}` |
| `app/Events/PagiMessageEdited.php` | Pesan diedit | `pagi-messages.{user_id}` |
| `app/Events/PagiMessageReacted.php` | Reaksi emoji ditambahkan | `pagi-messages.{user_id}` |
| `app/Events/PagiUnreadCountUpdated.php` | Jumlah unread berubah | `pagi-messages.{user_id}` |
| `app/Events/PagiWorkCreated.php` | Karya baru dibuat | (dispatch event) |
| `app/Events/PagiWorkUpdated.php` | Karya diperbarui | (dispatch event) |
| `app/Events/PagiWorkDeleted.php` | Karya dihapus | (dispatch event) |

### Notification

| File | Fungsi |
|---|---|
| `app/Notifications/PagiNotification.php` | Notifikasi umum modul PAGI (like, komentar, follow, kolaborasi, peringatan admin) |

### Middleware (Terkait PAGI)

| Middleware | Fungsi |
|---|---|
| `auth` | Autentikasi sesi Laravel |
| `EnsureFirstTimeLoginComplete` | Memastikan profil pertama kali sudah dilengkapi |
| `module.context:pagi` | Validasi konteks modul dan akses dasar |
| `module.context:pagi,super-admin,...` | Validasi akses admin dengan role tertentu |
| `throttle:pagi-api` | Rate limiting endpoint umum PAGI |
| `throttle:uploads` | Rate limiting upload file |
| `throttle:pagi-chat-send` | Rate limiting pengiriman pesan |
| `throttle:30,1` | Rate limiting like (max 30/menit) |
| `throttle:20,1` | Rate limiting komentar (max 20/menit) |
| `throttle:60,1` | Rate limiting preview data dan view count (max 60/menit) |

---

## G. TEKNOLOGI YANG DIGUNAKAN DAN FITUR YANG DIDUKUNG

| Teknologi | Digunakan Untuk | Bukti Implementasi |
|---|---|---|
| **Laravel 12 / PHP 8.4** | Framework backend seluruh modul | `routes/pagi.php`, seluruh Controller dan Service |
| **Inertia.js v2** | Bridge komunikasi Laravel-Vue (SPA navigation) | `Inertia::render()` di semua Controller |
| **Vue 3 + TypeScript** | Seluruh UI frontend modul PAGI | `resources/js/pages/Modules/Pagi/` |
| **Tailwind CSS v4** | Styling UI | Digunakan di semua file `.vue` |
| **Tiptap v3** | Block editor karya portofolio (rich-text) | `PagiTiptapEditor.vue`, `editorSuggestions.ts` |
| **Redis** | Cache feed galeri, cache profil, session, queue notifikasi | `Cache::remember()`, `Cache::forget()` di Model dan Service |
| **Meilisearch + Laravel Scout** | Full-text search karya di galeri | `PagiWork::searchableAs()`, `toSearchableArray()`, `shouldBeSearchable()` |
| **Laravel Reverb** | WebSocket server untuk chat real-time | `PagiMessageSent`, `PagiMessagesRead`, dll. |
| **Laravel Echo** | WebSocket client di frontend | `Messages.vue` |
| **Google Gemini AI (Text)** | Moderasi konten teks otomatis | `ContentModerationService::evaluateWithGoogleGeminiApi()` |
| **Google Gemini Vision** | Moderasi konten gambar otomatis | `ContentModerationService::evaluateImageWithGoogleGeminiApi()` |
| **Laravel Storage** | Penyimpanan file media (cover, sertifikat, foto CV) | `Storage::put()` di `PortfolioService`, `PagiCertificateService`, `PagiCvService` |
| **DomPDF** | Generate PDF dokumen CV | `PagiCvController::generatePdf()` menggunakan `Pdf::loadView()` |
| **intervention/image** | Kompresi gambar sebelum disimpan | `HandlesImageCompression::compressAndSaveImage()` di `PagiCertificateService`, `PagiCvService` |
| **Google OAuth 2.0** | Login via akun Google | Menggunakan Laravel Socialite (level Auth, bukan spesifik PAGI) |
| **PHP GD** | Analisis pixel gambar (deteksi NSFW lokal) | `ContentModerationService::evaluateImageLocalHeuristics()` baris 287–350 |
| **VirusScannerService** | Scan antivirus pada file PDF yang diupload sertifikat | `PagiCertificateService::store()` baris 77–80 |

**Tidak dapat diverifikasi penggunaan spesifik di PAGI:**
- `@ffmpeg/ffmpeg` (WebAssembly) — ditemukan di `package.json` dan `vite.config.ts` sebagai chunk terpisah; ada rule `VideoDurationRule` dan ekstensi file video yang diterima, tetapi tidak ditemukan pemanggilan eksplisit FFmpeg di kode PAGI yang dianalisis
- `jspdf` — ada di `package.json`; CV export menggunakan DomPDF server-side bukan jspdf (client-side); penggunaan jspdf di PAGI belum terverifikasi

---

## H. FITUR YANG DIKETAHUI SEMENTARA DINONAKTIFKAN

Berdasarkan source code di `routes/pagi.php`, terdapat route yang secara eksplisit diredirect dengan pesan "fitur sementara dinonaktifkan":

| Fitur | Route | Status |
|---|---|---|
| Pemilihan Template Works | `GET /pagi/works` | Dinonaktifkan, redirect ke dashboard |
| Preview Theme Works | `GET /pagi/works/preview/{theme}` | Dinonaktifkan |
| Editor Theme Works | `GET /pagi/works/editor/{theme}` | Dinonaktifkan |
| Simpan Works | `POST /pagi/works/save` | Dinonaktifkan |
| Pilih Works | `POST /pagi/works/select` | Dinonaktifkan |
| Profil Publik via Works URL | `GET /pagi/works/v/{user:pagi_username}` | Dinonaktifkan |

**Catatan:** Fitur-fitur di atas memiliki route yang terdefinisi namun dikembalikan sebagai redirect. Model `PagiCustomWork.php` juga ada di sistem, tapi fitur ini terkait dengan Works yang dinonaktifkan.

---

## I. SCREENSHOT YANG DIPERLUKAN UNTUK LAPORAN HKI

### Screenshot Wajib

1. **Halaman Dashboard Mahasiswa**
   - Halaman: `/pagi/`
   - Fitur yang dibuktikan: Feed karya, statistik, akses cepat ke editor
   - Yang harus terlihat: Feed karya, angka statistik, tombol buat karya

2. **Halaman Editor Karya**
   - Halaman: `/pagi/editor`
   - Fitur yang dibuktikan: Block editor, toolbar, canvas
   - Yang harus terlihat: Canvas editor, sidebar blok, toolbar Tiptap, tombol publish

3. **Modal Publikasi Karya**
   - Halaman: `/pagi/editor` (setelah klik Publish)
   - Fitur yang dibuktikan: Form metadata (judul, kategori, tag, tools, deskripsi, cover, visibility)
   - Yang harus terlihat: Form lengkap dengan semua field yang diwajibkan

4. **Halaman Galeri**
   - Halaman: `/pagi/gallery`
   - Fitur yang dibuktikan: Eksplorasi karya, daftar karya dari semua pengguna
   - Yang harus terlihat: Grid karya, judul, author, likes, views

5. **Modal Pratinjau Karya**
   - Halaman: `/pagi/gallery` → klik karya
   - Fitur yang dibuktikan: Konten blok, komentar, like, kolaborator
   - Yang harus terlihat: Konten lengkap, section komentar dengan reply

6. **Halaman Direct Message**
   - Halaman: `/pagi/messages`
   - Fitur yang dibuktikan: Chat real-time, daftar percakapan
   - Yang harus terlihat: Sidebar percakapan, area chat, form kirim pesan

7. **Halaman CV Builder**
   - Halaman: `/pagi/cv/{id}/edit`
   - Fitur yang dibuktikan: Editor CV interaktif, preview real-time
   - Yang harus terlihat: Editor section, preview CV, tombol download PDF

8. **Dashboard Admin**
   - Halaman: `/pagi/admin/`
   - Fitur yang dibuktikan: Statistik, grafik, moderasi overview
   - Yang harus terlihat: Statistik angka, grafik, daftar moderasi pending

9. **Halaman Laporan/Moderasi Admin**
   - Halaman: `/pagi/admin/reports`
   - Fitur yang dibuktikan: Daftar laporan dari user dan AI
   - Yang harus terlihat: Tabel laporan, status laporan, tombol tindakan

### Screenshot Pendukung

1. **Halaman Profil Publik**
   - Halaman: `/pagi/{username}`
   - Yang harus terlihat: Foto profil, bio, daftar karya, sertifikat, tombol follow

2. **Halaman People/Komunitas**
   - Halaman: `/pagi/people`
   - Yang harus terlihat: Daftar pengguna, followers count, karya terbaru

3. **Halaman Notifikasi**
   - Halaman: `/pagi/notifications`
   - Yang harus terlihat: Daftar notifikasi berbagai tipe (like, komentar, follow)

4. **Galeri Template CV**
   - Halaman: `/pagi/cv/templates`
   - Yang harus terlihat: Pilihan template CV yang tersedia

5. **Manajemen Sertifikat**
   - Halaman: Profil → tab sertifikat
   - Yang harus terlihat: Daftar sertifikat, form tambah sertifikat, upload media

6. **Kamus Kata Terlarang (Admin)**
   - Halaman: `/pagi/admin/text-dictionary`
   - Yang harus terlihat: Daftar kata terlarang, form tambah kata kustom

---

*Dokumen ini disusun berdasarkan audit langsung terhadap source code modul PAGI pada repository FMIKOM Portal.*
*Tanggal: 21 Agustus 2026*
*Seluruh klaim dapat ditelusuri ke file implementasi yang disebutkan pada masing-masing bagian.*
