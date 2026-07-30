<?php

namespace Database\Seeders;

use App\Models\Portal\PortalMenu;
use App\Models\Portal\PortalPage;
use Illuminate\Database\Seeder;

class PortalPagesSeeder extends Seeder
{
    /**
     * Run the database seeds for Portal Pages and Menus.
     */
    public function run(): void
    {
        $pagesData = [
            // ─── KATEGORI: PROFIL (9 Halaman) ──────────────────────────────────
            [
                'title' => 'Profil Fakultas FMIKOM',
                'slug' => 'profil-fakultas',
                'category' => 'Profil',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Gambaran umum Fakultas Matematika dan Ilmu Komputer (FMIKOM) UNUGHA.',
                'meta_description' => 'Profil singkat dan pengenalan Fakultas Matematika dan Ilmu Komputer (FMIKOM).',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Tentang FMIKOM</h2>
                        <p>Fakultas Matematika dan Ilmu Komputer (FMIKOM) merupakan salah satu fakultas unggulan di Universitas Nahdlatul Ulama Al Ghazali (UNUGHA) Cilacap. FMIKOM berkomitmen mencetak lulusan berdaya saing global, berintegritas tinggi, dan menguasai teknologi modern berbasis nilai-nilai keislaman.</p>
                        <h3>Fokus Utama Pengembangan</h3>
                        <ul>
                            <li>Pengembangan Sains Data, Kecerdasan Buatan (AI), dan Rekayasa Perangkat Lunak.</li>
                            <li>Penerapan Matematika Terapan dalam Pemodelan Industri dan Keuangan.</li>
                            <li>Pengembangan Sistem Informasi Manajemen dan Keamanan Siber.</li>
                        </ul>
                    </div>
                ',
            ],
            [
                'title' => 'Visi, Misi & Tujuan',
                'slug' => 'visi-misi-tujuan',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Visi, Misi, dan Nilai Utama Fakultas Matematika dan Ilmu Komputer.',
                'meta_description' => 'Visi dan Misi Strategis FMIKOM UNUGHA.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Visi Strategis</h2>
                        <blockquote class="p-4 border-l-4 border-emerald-500 bg-emerald-50 dark:bg-emerald-950/20 italic">
                            "Menjadi Fakultas Unggulan di Bidang Sains Data dan Teknologi Informasi Berbasis Nilai-Nilai Islam Ahlussunnah wal Jama\'ah pada Tahun 2030."
                        </blockquote>
                        <h2>Misi Utama</h2>
                        <ol>
                            <li>Menyelenggarakan pendidikan tinggi berkualitas di bidang Matematika dan Komputer.</li>
                            <li>Mengembangkan penelitian terapan dan inovasi teknologi bagi kemaslahatan masyarakat.</li>
                            <li>Melakukan pengabdian kepada masyarakat berbasis riset dan teknologi tepat guna.</li>
                            <li>Membangun jejaring kerjasama nasional dan internasional.</li>
                        </ol>
                    </div>
                ',
            ],
            [
                'title' => 'Sejarah Berdirinya FMIKOM',
                'slug' => 'sejarah-fakultas',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Perjalanan bersejarah pendirian dan perkembangan FMIKOM.',
                'meta_description' => 'Sejarah lengkap berdirinya Fakultas Matematika dan Ilmu Komputer.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Sejarah dan Perkembangan</h2>
                        <p>Fakultas Matematika dan Ilmu Komputer didirikan sebagai respon atas pesatnya perkembangan ilmu pengetahuan dan teknologi digital di Indonesia. Berawal dari integrasi program studi Matematika, Teknik Informatika, dan Sistem Informasi, FMIKOM terus bertransformasi menjadi pusat keunggulan pendidikan sains data dan teknologi informasi.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Struktur Organisasi',
                'slug' => 'struktur-organisasi',
                'category' => 'Profil',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Bagan pimpinan dan struktur tata kelola FMIKOM.',
                'meta_description' => 'Struktur organisasi dan jajaran pimpinan FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Tata Kelola & Pimpinan Fakultas</h2>
                        <p>Struktur pimpinan Fakultas Matematika dan Ilmu Komputer terdiri dari Dekan, Wakil Dekan, Ketua Program Studi, serta Kepala Laboratorium yang berdedikasi menciptakan suasana akademik yang kondusif.</p>
                        <ul>
                            <li><strong>Dekan:</strong> Dr. H. Ahmad Maruf, M.Kom.</li>
                            <li><strong>Wakil Dekan:</strong> Siti Rahmawati, M.T.</li>
                            <li><strong>Kaprodi Informatika:</strong> Budi Santoso, M.Cs.</li>
                            <li><strong>Kaprodi Sistem Informasi:</strong> Nurul Huda, M.Kom.</li>
                            <li><strong>Kaprodi Matematika:</strong> Eko Prasetyo, M.Si.</li>
                        </ul>
                    </div>
                ',
            ],
            [
                'title' => 'Sambutan Dekan',
                'slug' => 'sambutan-dekan',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Pesan ucapan selamat datang dari Dekan FMIKOM.',
                'meta_description' => 'Sambutan resmi dari Dekan Fakultas Matematika dan Ilmu Komputer.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Assalamu\'alaikum Warahmatullahi Wabarakatuh</h2>
                        <p>Selamat datang di portal resmi Fakultas Matematika dan Ilmu Komputer (FMIKOM). Di era transformasi digital ini, penguasaan ilmu komputer dan matematika dasar menjadi fondasi utama pembangunan peradaban modern.</p>
                        <p>Kami menyambut seluruh calon mahasiswa, peneliti, dan mitra industri untuk berkolaborasi bersama kami mencapai keunggulan sains dan teknologi.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Dosen & Staf Pengajar',
                'slug' => 'dosen-staf-pengajar',
                'category' => 'Profil',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Profil akademisi dan tenaga pengajar profesional FMIKOM.',
                'meta_description' => 'Daftar dosen dan staf pengajar berpengalaman FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Sumber Daya Manusia & Pengajar</h2>
                        <p>FMIKOM didukung oleh puluhan dosen bergelar Magister dan Doktor lulusan perguruan tinggi terkemuka dalam dan luar negeri dengan berbagai sertifikasi keahlian internasional.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Akreditasi Program Studi',
                'slug' => 'akreditasi-fakultas',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Status akreditasi resmi dari BAN-PT dan LAM INFOKOM.',
                'meta_description' => 'Informasi peringkat dan sertifikat akreditasi prodi di FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Peringkat Akreditasi Resmi</h2>
                        <p>Seluruh program studi di lingkungan FMIKOM telah terakreditasi resmi oleh Lembaga Akreditasi Mandiri (LAM INFOKOM / LAMSAMA) dan BAN-PT dengan peringkat BAIK SEKALI.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Fasilitas & Laboratorium',
                'slug' => 'fasilitas-laboratorium',
                'category' => 'Profil',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Sarana prasarana laboratorium komputer modern dan ruang kuliah ber-AC.',
                'meta_description' => 'Fasilitas laboratorium komputer, AI, dan jaringan di FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Sarana Prasarana Pembelajaran</h2>
                        <ul>
                            <li><strong>Laboratorium Software Engineering:</strong> Dilengkapi 40 PC High-End untuk pengembangan web & mobile.</li>
                            <li><strong>Laboratorium AI & Data Science:</strong> Dilengkapi Server GPU untuk pemrosesan Deep Learning.</li>
                            <li><strong>Laboratorium Jaringan & Cyber Security:</strong> Dilengkapi perangkat Cisco & Mikrotik Enterprise.</li>
                            <li><strong>Ruang Kuliah Multimedia:</strong> Dilengkapi Proyektor Interactive & AC di setiap kelas.</li>
                        </ul>
                    </div>
                ',
            ],
            [
                'title' => 'Kontak & Lokasi Kampus',
                'slug' => 'kontak-lokasi',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Alamat lengkap gedung FMIKOM, email resmi, dan layanan telepon.',
                'meta_description' => 'Informasi kontak dan peta lokasi gedung FMIKOM UNUGHA.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Hubungi Kami</h2>
                        <p><strong>Alamat:</strong> Gedung FMIKOM Kampus UNUGHA, Jl. Kemerdekaan No. 17, Cilacap.</p>
                        <p><strong>Email:</strong> info@fmikom.ac.id | hotline@fmikom.ac.id</p>
                        <p><strong>Telepon:</strong> (0282) 555-1234 / WhatsApp: +62 812-3456-7890</p>
                    </div>
                ',
            ],

            // ─── KATEGORI: AKADEMIK (6 Halaman) ────────────────────────────────
            [
                'title' => 'Program Studi Informatika',
                'slug' => 'informatika',
                'category' => 'Akademik',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Program Studi S1 Informatika fokus pada Software Development, AI, dan Cloud Engineering.',
                'meta_description' => 'Kurikulum dan profil lulusan Program Studi Informatika S1.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>S1 Informatika</h2>
                        <p>Program Studi Informatika menyiapkan mahasiswa menjadi Software Engineer, Full-Stack Developer, AI Specialist, dan Cloud Architect yang siap bersaing secara global.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Program Studi Sistem Informasi',
                'slug' => 'sistem-informasi',
                'category' => 'Akademik',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Program Studi S1 Sistem Informasi fokus pada Business Analytics dan Enterprise Architecture.',
                'meta_description' => 'Kurikulum dan keunggulan Program Studi Sistem Informasi S1.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>S1 Sistem Informasi</h2>
                        <p>Studi Sistem Informasi menjembatani antara kebutuhan bisnis industri dan solusi teknologi modern, mencetak IT Analyst, Business Intelligence, dan Project Manager.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Program Studi Matematika',
                'slug' => 'matematika',
                'category' => 'Akademik',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Program Studi S1 Matematika fokus pada Actuarial Science, Computational Math, dan Data Modeling.',
                'meta_description' => 'Profil lulusan dan spesialisasi Program Studi Matematika S1.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>S1 Matematika</h2>
                        <p>Mencetak Aktuaris, Data Analyst, dan Peneliti Matematika Terapan berpengalaman dalam pemodelan data finansial dan analisis statistik.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Kalender Akademik & Jadwal',
                'slug' => 'kalender-akademik-info',
                'category' => 'Akademik',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Jadwal perkuliahan, periode KRS, UTS, UAS, dan libur semester.',
                'meta_description' => 'Kalender akademik resmi FMIKOM tahun ajaran berjalan.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Kalender Akademik Semester Aktif</h2>
                        <p>Unduh jadwal lengkap kegiatan perkuliahan, batas pengisian KRS, ujian tengah semester, dan ujian akhir semester melalui tautan berikut.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Panduan Kurikulum & MBKM',
                'slug' => 'panduan-kurikulum',
                'category' => 'Akademik',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Informasi kurikulum berbasis Kerangka Kualifikasi Nasional Indonesia dan program MBKM.',
                'meta_description' => 'Struktur mata kuliah dan skema Merdeka Belajar Kampus Merdeka.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Kurikulum & Program MBKM</h2>
                        <p>FMIKOM menerapkan kurikulum Outcome-Based Education (OBE) yang terintegrasi dengan Magang Industri, Pertukaran Mahasiswa, dan Studi Independen Bersertifikat (MBKM).</p>
                    </div>
                ',
            ],
            [
                'title' => 'Prosedur Skripsi & Tugas Akhir',
                'slug' => 'prosedur-skripsi',
                'category' => 'Akademik',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Panduan pengajuan judul, sidang proposal, bimbingan, dan sidang munaqosyah.',
                'meta_description' => 'Alur dan syarat pendaftaran Skripsi bagi mahasiswa FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Alur Pengajuan & Ujian Skripsi</h2>
                        <ol>
                            <li>Pengajuan Proposal Skripsi via Portal Sistem WIMS.</li>
                            <li>Penetapan Dosen Pembimbing I dan II.</li>
                            <li>Pelaksanaan Seminar Proposal.</li>
                            <li>Bimbingan & Penyusunan Laporan Akhir.</li>
                            <li>Sidang Ujian Munaqosyah / Tugas Akhir.</li>
                        </ol>
                    </div>
                ',
            ],

            // ─── KATEGORI: BERITA & MEDIA (4 Halaman) ──────────────────────────
            [
                'title' => 'Pusat Pengumuman Resmi',
                'slug' => 'pusat-pengumuman',
                'category' => 'Berita & Media',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Pengumuman resmi surat keputusan fakultas dan edaran Dekan.',
                'meta_description' => 'Papan pengumuman informasi akademik dan kemahasiswaan FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Pengumuman Fakultas Terbaru</h2>
                        <p>Dapatkan update informasi resmi terkait beasiswa, seminar nasional, pendaftaran ujian, dan kegiatan operasional fakultas.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Prestasi Mahasiswa & Dosen',
                'slug' => 'prestasi-kemahasiswaan',
                'category' => 'Berita & Media',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Daftar kejuaraan kompetisi hackathon, gemastik, dan jurnal ilmiah terindeks.',
                'meta_description' => 'Catatan prestasi gemilang sivitas akademika FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Galeri Prestasi & Penghargaan</h2>
                        <p>Selamat kepada tim mahasiswa FMIKOM yang berhasil menjuarai berbagai kompetisi bidang teknologi informasi dan matematika tingkat nasional.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Galeri Kegiatan Kampus',
                'slug' => 'galeri-kegiatan',
                'category' => 'Berita & Media',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Dokumentasi kebersamaan kegiatan organisasi, workshop, dan Dies Natalis.',
                'meta_description' => 'Dokumentasi foto dan video kegiatan kampus FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Dokumentasi & Galeri Foto</h2>
                        <p>Kumpulan momen berharga kegiatan akademik, makrab mahasiswa, pelatihan koding, dan pameran karya produk digital.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Warta & Buletin FMIKOM',
                'slug' => 'buletin-warta',
                'category' => 'Berita & Media',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Publikasi berkala buletin fakultas dan warta digital.',
                'meta_description' => 'E-Magazine dan buletin seputar teknologi dan perkembangan FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Buletin Digital Edisi Terbaru</h2>
                        <p>Baca artikel populer, ulasan opini teknologi, serta wawancara eksklusif alumni FMIKOM di majalah buletin fakultas.</p>
                    </div>
                ',
            ],

            // ─── KATEGORI: LAYANAN (3 Halaman) ─────────────────────────────────
            [
                'title' => 'Layanan Surat Mahasiswa',
                'slug' => 'layanan-surat-mahasiswa',
                'category' => 'Layanan',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Pengajuan Surat Keterangan Aktif Kuliah, Surat Izin Penelitian, dan Surat Magang.',
                'meta_description' => 'Prosedur dan pembuatan surat menyurat akademik mahasiswa.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Layanan Administrasi Surat Online</h2>
                        <p>Mahasiswa dapat mengajukan berbagai jenis surat keterangan akademik secara cepat melalui integrasi modul Surat di Portal FMIKOM.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Helpdesk IT & Siakad/SINTA',
                'slug' => 'helpdesk-it',
                'category' => 'Layanan',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Bantuan reset password email kampus, Siakad UNUGHA, dan SINTA BIMA.',
                'meta_description' => 'Pusat bantuan teknis akun dan sistem informasi FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Layanan Bantuan Layanan IT</h2>
                        <p>Jika Anda mengalami kendala akses SSO, email instansi, atau sistem akademik Siakad, silakan hubungi tim Helpdesk IT FMIKOM.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Informasi Beasiswa & Bantuan',
                'slug' => 'informasi-beasiswa',
                'category' => 'Layanan',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Informasi pendaftaran Beasiswa KIP Kuliah, Beasiswa Prestasi, dan Mitra Industri.',
                'meta_description' => 'Informasi program beasiswa dan bantuan biaya pendidikan.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Program Beasiswa Berkelanjutan</h2>
                        <p>FMIKOM menyediakan berbagai skema bantuan pendidikan dan beasiswa bagi mahasiswa berprestasi dan kurang mampu.</p>
                    </div>
                ',
            ],
        ];

        // 1. Inisialisasi Halaman
        $createdPages = [];
        foreach ($pagesData as $data) {
            $page = PortalPage::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
            $createdPages[$data['slug']] = $page;
        }

        // 2. Inisialisasi Menu Utama Navigasi Publik
        $menus = [
            ['title' => 'Profil', 'category' => 'Profil', 'order' => 1],
            ['title' => 'Akademik', 'category' => 'Akademik', 'order' => 2],
            ['title' => 'Berita & Media', 'category' => 'Berita & Media', 'order' => 3],
            ['title' => 'Layanan', 'category' => 'Layanan', 'order' => 4],
        ];

        foreach ($menus as $menuItem) {
            $parentMenu = PortalMenu::firstOrCreate(
                ['title' => $menuItem['title'], 'parent_id' => null],
                ['order' => $menuItem['order']]
            );

            // Cari halaman-halaman yang masuk dalam kategori menu ini
            $childPages = array_filter($pagesData, fn($p) => $p['category'] === $menuItem['category']);
            $orderCount = 1;
            foreach ($childPages as $p) {
                if (isset($createdPages[$p['slug']])) {
                    PortalMenu::firstOrCreate(
                        [
                            'title' => $p['title'],
                            'parent_id' => $parentMenu->id,
                        ],
                        [
                            'portal_page_id' => $createdPages[$p['slug']]->id,
                            'order' => $orderCount++,
                        ]
                    );
                }
            }
        }
    }
}
