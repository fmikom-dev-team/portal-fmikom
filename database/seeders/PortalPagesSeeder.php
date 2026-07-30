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
                'excerpt' => 'Profil & gambaran umum Fakultas Matematika dan Ilmu Komputer.',
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
                'slug' => 'visi-misi',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Visi, misi & tujuan strategis Fakultas Matematika dan Ilmu Komputer.',
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
                'slug' => 'sejarah',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Perjalanan & latar belakang pendirian FMIKOM.',
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
                'excerpt' => 'Hierarki & divisi fakultas FMIKOM.',
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
                'slug' => 'dekan-kaprodi',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Pimpinan & kepala program pimpinan fakultas.',
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
                'slug' => 'dosen-staff',
                'category' => 'Profil',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Tenaga pengajar & kependidikan profesional.',
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
                'slug' => 'akreditasi',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Status & sertifikasi mutu BAN-PT dan LAM INFOKOM.',
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
                'slug' => 'fasilitas',
                'category' => 'Profil',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Sarana & prasarana kampus dan laboratorium komputer modern.',
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
                'slug' => 'kontak',
                'category' => 'Profil',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Lokasi & kontak resmi gedung FMIKOM.',
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

            // ─── KATEGORI: AKADEMIK (9 Halaman) ────────────────────────────────
            [
                'title' => 'Program Studi Informatika',
                'slug' => 'informatika',
                'category' => 'Akademik',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'S1 Teknik Informatika',
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
                'excerpt' => 'S1 Sistem Informasi',
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
                'excerpt' => 'S1 Matematika',
                'meta_description' => 'Profil lulusan dan spesialisasi Program Studi Matematika S1.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>S1 Matematika</h2>
                        <p>Mencetak Aktuaris, Data Analyst, dan Peneliti Matematika Terapan berpengalaman dalam pemodelan data finansial dan analisis statistik.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Kalender Akademik',
                'slug' => 'kalender-akademik',
                'category' => 'Akademik',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Jadwal & agenda akademik',
                'meta_description' => 'Kalender akademik resmi FMIKOM tahun ajaran berjalan.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Kalender Akademik Semester Aktif</h2>
                        <p>Unduh jadwal lengkap kegiatan perkuliahan, batas pengisian KRS, ujian tengah semester, dan ujian akhir semester melalui portal akademik.</p>
                    </div>
                ',
            ],
            [
                'title' => 'MBKM',
                'slug' => 'mbkm',
                'category' => 'Akademik',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Merdeka belajar kampus merdeka',
                'meta_description' => 'Skema dan pendaftaran program MBKM FMIKOM UNUGHA.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Program Merdeka Belajar Kampus Merdeka (MBKM)</h2>
                        <p>FMIKOM memberikan kesempatan konversi hingga 20 SKS untuk program Magang Industri, Pertukaran Mahasiswa, Studi Independen, dan Proyek Kemanusiaan.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Magang',
                'slug' => 'magang',
                'category' => 'Akademik',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Praktik kerja industri',
                'meta_description' => 'Panduan dan alur magang praktik kerja industri FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Operasional & Penempatan Magang</h2>
                        <p>Pelaksanaan magang dikelola secara terintegrasi melalui modul WIMS FMIKOM untuk pemantauan pembimbing lapangan dan penilaian akhir.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Pedoman Akademik',
                'slug' => 'pedoman-akademik',
                'category' => 'Akademik',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Panduan & aturan akademik',
                'meta_description' => 'Buku pedoman peraturan akademik FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Buku Pedoman Akademik</h2>
                        <p>Petunjuk teknis sistem kredit semester (SKS), syarat evaluasi studi, tata tertib perkuliahan, dan etika akademis mahasiswa.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Beasiswa',
                'slug' => 'beasiswa',
                'category' => 'Akademik',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Program bantuan & pendanaan',
                'meta_description' => 'Informasi beasiswa KIP, Prestasi, dan Kemitraan FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Informasi Skema Beasiswa</h2>
                        <p>FMIKOM memfasilitasi beasiswa KIP Kuliah, Beasiswa Yayasan Al Ghazali, Beasiswa Bank Indonesia, dan Beasiswa Industri Mitra.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Download Dokumen',
                'slug' => 'download-dokumen',
                'category' => 'Akademik',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Unduh form & dokumen resmi',
                'meta_description' => 'Pusat unduhan formulir dan template dokumen resmi FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Pusat Unduhan Formulir Resmi</h2>
                        <p>Silakan unduh berkas template proposal skripsi, lembar pengesahan, form bebas lab, dan surat rujukan akademik di halaman ini.</p>
                    </div>
                ',
            ],

            // ─── KATEGORI: BERITA & MEDIA (5 Halaman) ──────────────────────────
            [
                'title' => 'Berita',
                'slug' => 'berita',
                'category' => 'Berita & Media',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Berita terbaru FMIKOM',
                'meta_description' => 'Berita dan kabar terbaru kegiatan akademik FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Berita Terbaru Fakultas</h2>
                        <p>Informasi seputar agenda terkini, prestasi mahasiswa, dan liputan kegiatan di lingkungan FMIKOM.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Pengumuman',
                'slug' => 'pengumuman',
                'category' => 'Berita & Media',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Info & pengumuman resmi',
                'meta_description' => 'Papan pengumuman informasi akademik dan kemahasiswaan FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Pengumuman Resmi Fakultas</h2>
                        <p>Dapatkan update informasi resmi terkait beasiswa, seminar nasional, pendaftaran ujian, dan edaran Dekan.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Agenda Event',
                'slug' => 'agenda-event',
                'category' => 'Berita & Media',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Kegiatan & event kampus',
                'meta_description' => 'Jadwal seminar, workshop, dan event FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Agenda & Event Mendatang</h2>
                        <p>Ikuti berbagai kegiatan ilmiah, seminar teknologi, hackathon, dan workshop yang diselenggarakan oleh FMIKOM.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Galeri',
                'slug' => 'galeri',
                'category' => 'Berita & Media',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Foto & dokumentasi kegiatan',
                'meta_description' => 'Dokumentasi foto dan galeri kegiatan FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Galeri Foto Kegiatan</h2>
                        <p>Kumpulan dokumentasi visual berbagai aktivitas perkuliahan, wisuda, pelatihan, dan lomba mahasiswa.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Video',
                'slug' => 'video',
                'category' => 'Berita & Media',
                'template' => 'full-width',
                'is_published' => true,
                'excerpt' => 'Video & konten multimedia',
                'meta_description' => 'Kumpulan video profil dan dokumentasi FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Video & Konten Multimedia</h2>
                        <p>Tonton video profil fakultas, liputan event, serta rekaman kuliah umum di saluran video FMIKOM.</p>
                    </div>
                ',
            ],

            // ─── KATEGORI: LAYANAN (3 Halaman) ─────────────────────────────────
            [
                'title' => 'Pengajuan Dokumen',
                'slug' => 'pengajuan-dokumen',
                'category' => 'Layanan',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Ajukan surat & dokumen',
                'meta_description' => 'Layanan pembuat dan pengajuan surat mahasiswa.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Layanan Pengajuan Dokumen Online</h2>
                        <p>Mahasiswa dapat mengajukan Surat Keterangan Aktif, Surat Penelitian, dan rekomendasi akademik secara mudah.</p>
                    </div>
                ',
            ],
            [
                'title' => 'Konsultasi Akademik',
                'slug' => 'konsultasi-akademik',
                'category' => 'Layanan',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Tanya jawab dengan dosen',
                'meta_description' => 'Layanan konseling dan konsultasi studi mahasiswa.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Layanan Bimbingan & Konsultasi Akademik</h2>
                        <p>Fasilitas komunikasi dan konsultasi mengenai perencanaan studi dan kendala akademis bersama dosen wali.</p>
                    </div>
                ',
            ],
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'category' => 'Layanan',
                'template' => 'default',
                'is_published' => true,
                'excerpt' => 'Pertanyaan yang sering diajukan',
                'meta_description' => 'Pusat jawaban pertanyaan populer mahasiswa FMIKOM.',
                'content' => '
                    <div class="prose max-w-none">
                        <h2>Pertanyaan Sering Diajukan (FAQ)</h2>
                        <p>Temukan jawaban cepat atas pertanyaan seputar pembayaran UKT, KRS, aktivasi SSO, dan layanan laboratorium.</p>
                    </div>
                ',
            ],
        ];

        // 1. Inisialisasi Halaman
        $createdPages = [];
        foreach ($pagesData as $data) {
            $page = PortalPage::updateOrCreate(
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
            $childPages = array_filter($pagesData, fn ($p) => $p['category'] === $menuItem['category']);
            $orderCount = 1;
            foreach ($childPages as $p) {
                if (isset($createdPages[$p['slug']])) {
                    PortalMenu::updateOrCreate(
                        [
                            'parent_id' => $parentMenu->id,
                            'portal_page_id' => $createdPages[$p['slug']]->id,
                        ],
                        [
                            'title' => $p['title'],
                            'order' => $orderCount++,
                        ]
                    );
                }
            }
        }
    }
}
