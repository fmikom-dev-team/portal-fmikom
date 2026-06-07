<?php

namespace Database\Seeders;
use App\Models\Surat\Surat;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Portal\PortalSetting;
use App\Models\Portal\PortalMenu;
use App\Models\Portal\PortalPost;
use App\Models\Portal\PortalPage;
use App\Models\Pagi\PagiWork;
use App\Models\Portal\PortalAcademicCalendar;
use App\Models\Portal\PortalEvent;
use Illuminate\Support\Facades\Cache;

class PortalDummySeeder extends Seeder // NOSONAR
{
    const CATEGORY_MEDIA = 'Berita & Media';
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    public function run(): void
    {
        // Ambil user pertama sebagai author
        $user = User::first();
        if (!$user) {
            $this->command->error('Silakan run DummyUserSeeder terlebih dahulu!');
            return;
        }

        // Hapus data lama untuk seeder ulang yang bersih
        PortalMenu::query()->delete();
        PortalPage::query()->delete();
        PortalPost::query()->delete();
        PagiWork::query()->delete();

        // 1. Seed Portal Settings
        $this->seedSettings();

        // 2. Create Portal Pages for sub-menus
        $pageModels = $this->seedPages();

        // 3 & 4. Seed Menus
        $this->seedMenus($pageModels);

        // 5. Seed Portal Posts (Berita)
        $this->seedPosts($user);

        // 6. Seed PAGI Portfolios (Karya)
        $this->seedPortfolios($user);

        // 7. Seed Academic Calendar Events
        $this->seedAcademicEvents();

        // 8. Seed Campus Events
        $this->seedCampusEvents();

        // Clear Caches
        Cache::forget('portal_settings');
        Cache::forget('portal_menus');
        Cache::forget('portal_featured_posts');
        Cache::forget('portal_latest_posts');

        $this->command->info('Portal Dummy Seeder dengan menu lengkap berhasil dijalankan!');
    }

    private function seedSettings(): void
    {
        $settings = [
            'brand_name' => 'FMIKOM Portal',
            'show_navbar' => '1',
            'show_hero' => '1',
            'show_features' => '1',
            'show_partners' => '1',
            'show_benefits' => '1',
            'hero_title' => "Satu Portal untuk\nSemua Layanan\nFMIKOM",
            'hero_subtitle' => 'Sistem Informasi Terpadu',
            'hero_description' => 'Kelola administrasi, magang, alumni, dan portofolio dalam satu sistem terintegrasi. Dibangun untuk memberikan pengalaman terbaik bergaya modern.',
            'hero_gallery' => json_encode([
                'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=2670&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2670&auto=format&fit=crop'
            ]),
            'partners' => json_encode([
                ['name' => 'Google', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg'],
                ['name' => 'Microsoft', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg'],
                ['name' => 'IBM', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg']
            ]),
            'benefits_title' => 'Mengapa Memilih Portal FMIKOM?',
            'benefits_subtitle' => 'Kami membawa standarisasi aplikasi industri (SaaS) ke lingkungan kampus. Transparan, terpusat, dan mudah diakses.',
            'benefit_1_title' => 'Akses Mudah',
            'benefit_1_desc' => 'Satu platform untuk semua layanan akademik dan administratif.',
            'benefit_2_title' => 'Data Real-Time',
            'benefit_2_desc' => 'Informasi selalu terkini dan akurat langsung dari sumbernya.',
            'benefit_3_title' => 'Keamanan Tinggi',
            'benefit_3_desc' => 'Sistem SSO dengan proteksi berlapis menjaga keamanan data.'
        ];

        foreach ($settings as $key => $value) {
            PortalSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedPages(): array
    {
        $subPages = array_merge(
            $this->getProfilPages(),
            $this->getAkademikPages(),
            $this->getMediaPages(),
            $this->getLayananPages()
        );

        $pageModels = [];
        foreach ($subPages as $p) {
            $pageModels[$p['slug']] = PortalPage::create([
                'title' => $p['title'],
                'slug' => $p['slug'],
                'category' => $p['category'],
                'content' => $p['content'],
                'excerpt' => $p['title'] . ' FMIKOM',
                'is_published' => true
            ]);
        }

        return $pageModels;
    }

    private function getTentangPage(): array
    {
        return [
            'title' => 'Tentang FMIKOM',
            'slug' => 'tentang-fmikom',
            'category' => 'Profil',
            'content' => <<<HTML
<p class="lead">Fakultas Ilmu Komputer (FMIKOM) adalah pusat keunggulan akademik and inovasi teknologi yang didedikasikan untuk mendidik generasi pemimpin digital masa depan.</p>
<div class="my-8 rounded-2xl bg-linear-to-r from-blue-50 to-indigo-50 p-6 dark:from-slate-800 dark:to-slate-900 border border-blue-100 dark:border-slate-800">
    <h3 class="mt-0 text-blue-900 dark:text-blue-200">Sekilas FMIKOM</h3>
    <p class="mb-0">Berdiri sejak tahun 2015, FMIKOM berkomitmen tinggi untuk menyelenggarakan pendidikan berkualitas dunia di bidang teknologi informasi. Kami menggabungkan kurikulum berbasis kompetensi industri dengan penelitian terapan untuk menghasilkan lulusan yang siap bersaing secara global.</p>
</div>
<h2>Nilai-Nilai Utama Kami</h2>
<ul class="grid grid-cols-1 md:grid-cols-2 gap-4 list-none pl-0">
    <li class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700/50">
        <strong class="text-blue-600 block text-lg mb-1">🚀 Inovasi Berkelanjutan</strong>
        Selalu adaptif terhadap perkembangan teknologi terbaru demi relevansi kurikulum.
    </li>
    <li class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700/50">
        <strong class="text-indigo-600 block text-lg mb-1">🤝 Integritas & Kolaborasi</strong>
        Membangun ekosistem akademik yang transparan, jujur, dan berorientasi kemitraan.
    </li>
</ul>
HTML
        ];
    }

    private function getSejarahPage(): array
    {
        return [
            'title' => 'Sejarah',
            'slug' => 'sejarah',
            'category' => 'Profil',
            'content' => <<<HTML
<p class="lead">Perjalanan FMIKOM dari masa ke masa menunjukkan komitmen kami yang teguh terhadap kualitas dan inovasi pendidikan tinggi komputer di Indonesia.</p>
<div class="relative border-l border-blue-200 dark:border-slate-700 my-10 ml-4 pl-6 space-y-8">
    <div class="relative">
        <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-blue-600 ring-4 ring-white dark:ring-slate-900"></div>
        <h3 class="font-bold text-lg text-slate-800 dark:text-white mt-0 mb-1">Tahun 2015 — Pendirian Fakultas</h3>
        <p class="text-slate-600 dark:text-slate-300">Fakultas Ilmu Komputer resmi berdiri dengan Program Studi S1 Teknik Informatika sebagai cikal bakal pembentukan fakultas.</p>
    </div>
    <div class="relative">
        <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-indigo-600 ring-4 ring-white dark:ring-slate-900"></div>
        <h3 class="font-bold text-lg text-slate-800 dark:text-white mt-0 mb-1">Tahun 2018 — Ekspansi Program Studi</h3>
        <p class="text-slate-600 dark:text-slate-300">Pembukaan Program Studi S1 Sistem Informasi untuk memenuhi kebutuhan analisis bisnis dan integrasi sistem korporasi.</p>
    </div>
    <div class="relative">
        <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-purple-600 ring-4 ring-white dark:ring-slate-900"></div>
        <h3 class="font-bold text-lg text-slate-800 dark:text-white mt-0 mb-1">Tahun 2021 — Akreditasi Unggul & Lab Baru</h3>
        <p class="text-slate-600 dark:text-slate-300">Meraih akreditasi "Baik Sekali" serta meresmikan Integrated IoT and Robotics Laboratory berkat kerja sama dengan mitra teknologi global.</p>
    </div>
    <div class="relative">
        <div class="absolute -left-[31px] top-1.5 w-4 h-4 rounded-full bg-emerald-600 ring-4 ring-white dark:ring-slate-900"></div>
        <h3 class="font-bold text-lg text-slate-800 dark:text-white mt-0 mb-1">Tahun 2025 s/d Sekarang — Pusat Riset AI & Cloud</h3>
        <p class="text-slate-600 dark:text-slate-300">FMIKOM bertransformasi menjadi pelopor riset AI terapan di wilayah regional dengan mendirikan AI Center of Excellence.</p>
    </div>
</div>
HTML
        ];
    }

    private function getVisiMisiPage(): array
    {
        return [
            'title' => 'Visi & Misi',
            'slug' => 'visi-misi',
            'category' => 'Profil',
            'content' => <<<HTML
<div class="text-center my-10 p-8 rounded-2xl bg-linear-to-b from-blue-50/50 to-indigo-50/20 dark:from-slate-800/30 border border-blue-100/50 dark:border-slate-800">
    <h2 class="text-2xl font-bold text-blue-900 dark:text-blue-300 mt-0">Visi FMIKOM</h2>
    <blockquote class="border-l-0 text-xl font-medium italic text-slate-700 dark:text-slate-200 my-4 pl-0">
        "Menjadi Fakultas Ilmu Komputer yang unggul di tingkat internasional dalam menghasilkan inovator teknologi yang berakhlak mulia dan berjiwa technopreneurship pada tahun 2030."
    </blockquote>
</div>
<h2>Misi FMIKOM</h2>
<ol class="space-y-4">
    <li>
        <strong>Pendidikan Berkualitas Dunia:</strong> Menyelenggarakan pendidikan tinggi di bidang komputer dengan standar internasional untuk menghasilkan lulusan yang kompeten dan adaptif.
    </li>
    <li>
        <strong>Penelitian Terapan Inovatif:</strong> Mengembangkan penelitian terapan yang inovatif dan solutif terhadap permasalahan riil masyarakat dan industri.
    </li>
    <li>
        <strong>Pengabdian Masyarakat Nyata:</strong> Menyebarluaskan ilmu pengetahuan dan hasil riset teknologi guna meningkatkan kualitas hidup masyarakat.
    </li>
    <li>
        <strong>Kemitraan Global:</strong> Membangun kerja sama sinergis dengan berbagai institusi nasional dan internasional dalam skema tri dharma perguruan tinggi.
    </li>
</ol>
HTML
        ];
    }

    private function getProdiPage(): array
    {
        return [
            'title' => 'Program Studi',
            'slug' => 'program-studi',
            'category' => 'Profil',
            'content' => <<<HTML
<p class="lead">Kami menawarkan program studi modern yang dirancang untuk membekali mahasiswa dengan keterampilan praktis dan teoretis yang sangat dibutuhkan industri digital.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-10 not-prose">
    <div class="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
        <span class="px-3 py-1 text-xs font-semibold text-blue-600 bg-blue-50 dark:bg-blue-900/30 rounded-full">S1 - Reguler</span>
        <h3 class="text-xl font-bold text-slate-800 dark:text-white mt-3 mb-2">Teknik Informatika</h3>
        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-4">Berfokus pada software engineering, kecerdasan buatan (AI), keamanan siber, dan pengembangan sistem terdistribusi.</p>
        <div class="flex items-center justify-between text-xs font-medium text-slate-400">
            <span>Akreditasi: <strong>Unggul</strong></span>
            <span>Durasi: 4 Tahun</span>
        </div>
    </div>
    <div class="p-6 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow">
        <span class="px-3 py-1 text-xs font-semibold text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 rounded-full">S1 - Reguler</span>
        <h3 class="text-xl font-bold text-slate-800 dark:text-white mt-3 mb-2">Sistem Informasi</h3>
        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-4">Mengintegrasikan teknologi informasi dengan proses bisnis untuk merancang solusi tata kelola enterprise dan analisis data.</p>
        <div class="flex items-center justify-between text-xs font-medium text-slate-400">
            <span>Akreditasi: <strong>Baik Sekali</strong></span>
            <span>Durasi: 4 Tahun</span>
        </div>
    </div>
</div>
HTML
        ];
    }

    private function getStrukturPage(): array
    {
        return [
            'title' => 'Struktur Organisasi',
            'slug' => 'struktur-organisasi',
            'category' => 'Profil',
            'content' => <<<HTML
<p class="lead">Struktur tata pamong di Fakultas Ilmu Komputer menjamin akuntabilitas, transparansi, dan efisiensi koordinasi akademik maupun operasional.</p>
<div class="my-8 p-6 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 not-prose">
    <h3 class="text-center mt-0 text-slate-800 dark:text-white font-bold text-base">Bagan Kepemimpinan Fakultas</h3>
    <div class="flex flex-col items-center justify-center space-y-4 my-6">
        <div class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow font-semibold text-sm">DEKAN FAKULTAS</div>
        <div class="w-px h-6 bg-slate-300 dark:bg-slate-600"></div>
        <div class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow font-semibold text-sm">WAKIL DEKAN</div>
        <div class="w-px h-6 bg-slate-300 dark:bg-slate-600"></div>
        <div class="grid grid-cols-2 gap-4 w-full max-w-md">
            <div class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-center rounded-lg text-xs font-semibold">KAPRODI INFORMATIKA</div>
            <div class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-center rounded-lg text-xs font-semibold">KAPRODI SISTEM INFORMASI</div>
        </div>
    </div>
</div>
HTML
        ];
    }

    private function getDekanKaprodiPage(): array
    {
        return [
            'title' => 'Dekan & Kaprodi',
            'slug' => 'dekan-kaprodi',
            'category' => 'Profil',
            'content' => <<<HTML
<p class="lead">Kenali pimpinan Fakultas Ilmu Komputer yang berdedikasi tinggi dalam mengawal visi akademik dan pelayanan mahasiswa.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-10 not-prose">
    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
        <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-inner shrink-0">
            DM
        </div>
        <div>
            <h4 class="font-bold text-slate-800 dark:text-white m-0 text-base">Dr. Muchlisin Maruf, M.T.</h4>
            <p class="text-xs text-slate-400 m-0 mt-0.5">Dekan FMIKOM</p>
            <p class="text-xs text-slate-500 m-0 mt-1">Bidang Keahlian: Artificial Intelligence & Cloud Computing</p>
        </div>
    </div>
    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
        <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-inner shrink-0">
            AS
        </div>
        <div>
            <h4 class="font-bold text-slate-800 dark:text-white m-0 text-base">Ahmad Setiawan, M.Kom.</h4>
            <p class="text-xs text-slate-400 m-0 mt-0.5">Kaprodi Teknik Informatika</p>
            <p class="text-xs text-slate-500 m-0 mt-1">Bidang Keahlian: Software Architecture & Cyber Security</p>
        </div>
    </div>
    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 col-span-1 md:col-span-2 md:max-w-md md:mx-auto w-full">
        <div class="w-16 h-16 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-lg shadow-inner shrink-0">
            RM
        </div>
        <div>
            <h4 class="font-bold text-slate-800 dark:text-white m-0 text-base">Rina Melati, M.S.I.</h4>
            <p class="text-xs text-slate-400 m-0 mt-0.5">Kaprodi Sistem Informasi</p>
            <p class="text-xs text-slate-500 m-0 mt-1">Bidang Keahlian: Enterprise Resource Planning & Big Data</p>
        </div>
    </div>
</div>
HTML
        ];
    }

    private function getDosenStaffPage(): array
    {
        return [
            'title' => 'Dosen & Staff',
            'slug' => 'dosen-staff',
            'category' => 'Profil',
            'content' => <<<HTML
<p class="lead">FMIKOM memiliki staf pengajar berkualifikasi tinggi lulusan universitas terkemuka dalam dan luar negeri, aktif dalam riset, publikasi ilmiah, dan pengabdian masyarakat.</p>
<div class="my-8 overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 my-0">
        <thead class="bg-slate-50 dark:bg-slate-800/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Dosen</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">NIDN</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Konsentrasi Utama</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-sm">
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Dr. Muchlisin Maruf, M.T.</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">0412098701</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Machine Learning, Big Data Analytics</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Ahmad Setiawan, M.Kom.</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">0415088203</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Software Engineering, Web Technologies</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Rina Melati, M.S.I.</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">0422118902</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Information System Security, IT Governance</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Budi Pratama, M.T.</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">0407029104</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Internet of Things, Embedded System</td>
            </tr>
        </tbody>
    </table>
</div>
HTML
        ];
    }

    private function getAkreditasiPage(): array
    {
        return [
            'title' => 'Akreditasi',
            'slug' => 'akreditasi',
            'category' => 'Profil',
            'content' => <<<HTML
<p class="lead">Komitmen FMIKOM terhadap penjaminan mutu pendidikan dibuktikan dengan raihan sertifikat akreditasi nasional yang diakui resmi oleh BAN-PT.</p>
<div class="my-8 overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 my-0">
        <thead class="bg-slate-50 dark:bg-slate-800/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Program Studi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jenjang</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Peringkat Akreditasi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Berlaku S/D</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-sm">
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Teknik Informatika</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">S1</td>
                <td class="px-4 py-3"><span class="px-2.5 py-0.5 text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/30 rounded-full">UNGGUL</span></td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">12 Desember 2029</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Sistem Informasi</td>
                <td class="px-4 py-3 text-slate-500 dark:text-slate-400">S1</td>
                <td class="px-4 py-3"><span class="px-2.5 py-0.5 text-xs font-semibold text-blue-600 bg-blue-50 dark:bg-blue-950/30 rounded-full">BAIK SEKALI</span></td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">18 Juni 2028</td>
            </tr>
        </tbody>
    </table>
</div>
HTML
        ];
    }

    private function getFasilitasPage(): array
    {
        return [
            'title' => 'Fasilitas',
            'slug' => 'fasilitas',
            'category' => 'Profil',
            'content' => <<<HTML
<p class="lead">Untuk menunjang proses perkuliahan dan riset, FMIKOM menyediakan berbagai sarana prasarana modern kelas dunia.</p>
<div class="space-y-6 my-10 not-prose">
    <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
        <h4 class="font-bold text-slate-800 dark:text-white mt-0 mb-1 text-base">🤖 Integrated IoT & Robotics Lab</h4>
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-0">Laboratorium yang dilengkapi dengan development board ESP32, Arduino, Raspberry Pi, 3D Printer, dan sensor modul terlengkap untuk kebutuhan riset embedded system.</p>
    </div>
    <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
        <h4 class="font-bold text-slate-800 dark:text-white mt-0 mb-1 text-base">🖥️ Artificial Intelligence Center</h4>
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-0">Menyediakan workstation berspesifikasi tinggi (GPU NVIDIA RTX) untuk akselerasi pengolahan model deep learning dan komputasi performa tinggi.</p>
    </div>
    <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40">
        <h4 class="font-bold text-slate-800 dark:text-white mt-0 mb-1 text-base">📚 Smart Library & Lounge</h4>
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-0">Perpustakaan digital dengan koleksi e-journal internasional ternama (IEEE, ACM) serta area diskusi mahasiswa yang nyaman dan dilengkapi high-speed Wi-Fi.</p>
    </div>
</div>
HTML
        ];
    }

    private function getProfilPages(): array
    {
        return [
            $this->getTentangPage(),
            $this->getSejarahPage(),
            $this->getVisiMisiPage(),
            $this->getProdiPage(),
            $this->getStrukturPage(),
            $this->getDekanKaprodiPage(),
            $this->getDosenStaffPage(),
            $this->getAkreditasiPage(),
            $this->getFasilitasPage()
        ];
    }

    private function getKalenderAkademikPage(): array
    {
        return [
            'title' => 'Kalender Akademik',
            'slug' => 'kalender-akademik',
            'category' => 'Akademik',
            'content' => <<<HTML
<p class="lead">Berikut adalah jadwal kegiatan akademik Fakultas Ilmu Komputer untuk Semester Ganjil Tahun Ajaran 2026/2027.</p>
<div class="my-8 overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 my-0">
        <thead class="bg-slate-50 dark:bg-slate-800/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kegiatan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal Mulai</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal Selesai</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-sm">
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Registrasi Ulang & Pembayaran UKT</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">1 Agustus 2026</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">15 Agustus 2026</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Pengisian KRS (Kartu Rencana Studi)</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">18 Agustus 2026</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">25 Agustus 2026</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Perkuliahan Semester Ganjil (Minggu 1-7)</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">1 September 2026</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">17 Oktober 2026</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Ujian Tengah Semester (UTS)</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">20 Oktober 2026</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">27 Oktober 2026</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Perkuliahan Semester Ganjil (Minggu 8-14)</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">2 November 2026</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">18 Desember 2026</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Ujian Akhir Semester (UAS)</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">21 Desember 2026</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">30 Desember 2026</td>
            </tr>
        </tbody>
    </table>
</div>
HTML
        ];
    }

    private function getMbkmPage(): array
    {
        return [
            'title' => 'MBKM',
            'slug' => 'mbkm',
            'category' => 'Akademik',
            'content' => <<<HTML
<p class="lead">Program Merdeka Belajar Kampus Merdeka (MBKM) memberikan kesempatan bagi mahasiswa untuk mengasah kemampuan sesuai bakat dan minat dengan terjun langsung ke dunia kerja.</p>
<h2>Jenis Program MBKM di FMIKOM</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 list-none pl-0 my-8 not-prose">
    <div class="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <h3 class="font-bold text-slate-800 dark:text-white m-0 text-base">💼 Magang Bersertifikat</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Magang kerja di industri teknologi terkemuka selama 1-2 semester yang diakui hingga 20 SKS.</p>
    </div>
    <div class="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <h3 class="font-bold text-slate-800 dark:text-white m-0 text-base">🎓 Studi Independen Bersertifikat</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Mempelajari kompetensi spesifik (misal: Cloud Practitioner, Data Scientist) dari partner edukasi resmi.</p>
    </div>
    <div class="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <h3 class="font-bold text-slate-800 dark:text-white m-0 text-base">✈️ Pertukaran Mahasiswa Merdeka</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Kuliah lintas kampus di seluruh Indonesia untuk merasakan kebhinekaan budaya dan sistem akademik.</p>
    </div>
    <div class="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
        <h3 class="font-bold text-slate-800 dark:text-white m-0 text-base">🏫 Kampus Mengajar</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Membantu proses pembelajaran literasi dan numerasi di jenjang SD dan SMP terpencil.</p>
    </div>
</div>
HTML
        ];
    }

    private function getMagangPage(): array
    {
        return [
            'title' => 'Magang',
            'slug' => 'magang',
            'category' => 'Akademik',
            'content' => <<<HTML
<p class="lead">Program Magang Kerja Industri FMIKOM dirancang untuk menyelaraskan keahlian akademis mahasiswa dengan praktik nyata di lapangan.</p>
<div class="my-8 p-6 bg-linear-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 border border-blue-100 dark:border-slate-800 rounded-2xl">
    <h3 class="mt-0 text-blue-900 dark:text-blue-200 text-lg">💡 Alur Pengajuan Magang (WIMS)</h3>
    <ol class="mb-0 text-sm space-y-2">
        <li>Mahasiswa mengajukan pendaftaran magang melalui sistem terpadu <strong>WIMS</strong>.</li>
        <li>Fakultas melakukan verifikasi nilai & prasyarat akademik (minimal lulus 90 SKS).</li>
        <li>Fakultas menerbitkan Surat Pengantar Magang resmi ke instansi mitra.</li>
        <li>Mahasiswa melaksanakan magang (minimal 3 bulan) dan mengisi logbook harian di portal.</li>
        <li>Penilaian oleh pembimbing lapangan industri dan dosen pembimbing magang.</li>
    </ol>
</div>
HTML
        ];
    }

    private function getPedomanAkademikPage(): array
    {
        return [
            'title' => 'Pedoman Akademik',
            'slug' => 'pedoman-akademik',
            'category' => 'Akademik',
            'content' => <<<HTML
<p class="lead">Buku Pedoman Akademik FMIKOM berisi peraturan, hak, kewajiban, serta panduan kurikulum bagi mahasiswa dalam menempuh studi.</p>
<div class="p-6 border border-slate-200 dark:border-slate-700 rounded-2xl my-8">
    <h3 class="mt-0 text-base">Bab Utama Buku Pedoman</h3>
    <ul class="space-y-2 text-sm">
        <li><strong>Bab I:</strong> Ketentuan Umum & Visi Misi Fakultas</li>
        <li><strong>Bab II:</strong> Sistem Kredit Semester (SKS) & Penilaian Hasil Belajar</li>
        <li><strong>Bab III:</strong> Beban Studi, Evaluasi Keberhasilan, & Batas Masa Studi</li>
        <li><strong>Bab IV:</strong> Ujian Skripsi, Yudisium, & Wisuda</li>
        <li><strong>Bab V:</strong> Kode Etik Mahasiswa & Tata Tertib Kehidupan Kampus</li>
    </ul>
    <p class="mb-0 text-sm text-slate-500">Buku pedoman lengkap dapat diunduh pada halaman <a href="/halaman/download-dokumen">Download Dokumen</a>.</p>
</div>
HTML
        ];
    }

    private function getBeasiswaPage(): array
    {
        return [
            'title' => 'Beasiswa',
            'slug' => 'beasiswa',
            'category' => 'Akademik',
            'content' => <<<HTML
<p class="lead">Kami mendukung mahasiswa berprestasi dan mahasiswa yang membutuhkan bantuan keuangan melalui berbagai program beasiswa internal maupun eksternal.</p>
<div class="space-y-4 my-8 not-prose">
    <div class="p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
        <h3 class="font-bold text-slate-800 dark:text-white m-0 text-base">🌟 Beasiswa KIP Kuliah</h3>
        <p class="text-xs text-slate-500 mt-1">Sumber: Kementerian Pendidikan & Kebudayaan RI</p>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Bantuan biaya kuliah penuh dan uang saku bulanan bagi mahasiswa berprestasi dari keluarga kurang mampu secara ekonomi.</p>
    </div>
    <div class="p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
        <h3 class="font-bold text-slate-800 dark:text-white m-0 text-base">🏆 Beasiswa Prestasi Akademik (PPA)</h3>
        <p class="text-xs text-slate-500 mt-1">Sumber: Dana Internal Yayasan & Kampus</p>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Diberikan setiap semester kepada mahasiswa dengan IPK tertinggi di masing-masing angkatan program studi.</p>
    </div>
    <div class="p-5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm">
        <h3 class="font-bold text-slate-800 dark:text-white m-0 text-base">🏢 Beasiswa Kemitraan Industri</h3>
        <p class="text-xs text-slate-500 mt-1">Sumber: Google, Microsoft & Bank Partner</p>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Bantuan finansial bersyarat disertai kesempatan magang langsung dan prioritas rekrutmen kerja setelah lulus.</p>
    </div>
</div>
HTML
        ];
    }

    private function getDownloadDokumenPage(): array
    {
        return [
            'title' => 'Download Dokumen',
            'slug' => 'download-dokumen',
            'category' => 'Akademik',
            'content' => <<<HTML
<p class="lead">Unduh semua formulir, panduan, dan dokumen kelengkapan administratif akademik Fakultas Ilmu Komputer di bawah ini.</p>
<ul class="space-y-3 pl-0 list-none my-8 not-prose">
    <li class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl">
        <div>
            <h4 class="font-semibold text-slate-800 dark:text-white m-0 text-sm">Buku Pedoman Akademik FMIKOM 2026/2027</h4>
            <p class="text-xs text-slate-400 m-0 mt-0.5">PDF • 4.2 MB</p>
        </div>
        <a href="#" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold no-underline transition-colors">Unduh</a>
    </li>
    <li class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl">
        <div>
            <h4 class="font-semibold text-slate-800 dark:text-white m-0 text-sm">Formulir Pengajuan Beban Studi & KRS Susulan</h4>
            <p class="text-xs text-slate-400 m-0 mt-0.5">DOCX • 180 KB</p>
        </div>
        <a href="#" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold no-underline transition-colors">Unduh</a>
    </li>
    <li class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl">
        <div>
            <h4 class="font-semibold text-slate-800 dark:text-white m-0 text-sm">Template Laporan Akhir Magang Industri</h4>
            <p class="text-xs text-slate-400 m-0 mt-0.5">DOCX • 240 KB</p>
        </div>
        <a href="#" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold no-underline transition-colors">Unduh</a>
    </li>
</ul>
HTML
        ];
    }

    private function getAkademikPages(): array
    {
        return [
            $this->getKalenderAkademikPage(),
            $this->getMbkmPage(),
            $this->getMagangPage(),
            $this->getPedomanAkademikPage(),
            $this->getBeasiswaPage(),
            $this->getDownloadDokumenPage()
        ];
    }

    private function getMediaPages(): array
    {
        return [
            [
                'title' => 'Pengumuman',
                'slug' => 'pengumuman',
                'category' => self::CATEGORY_MEDIA,
                'content' => <<<HTML
<p class="lead">Dapatkan informasi resmi dan pengumuman administratif terkini dari jajaran dekanat dan tata usaha Fakultas Ilmu Komputer.</p>
<div class="space-y-6 my-8 not-prose">
    <div class="p-5 border-l-4 border-blue-600 bg-slate-50 dark:bg-slate-800 rounded-r-xl">
        <span class="text-xs font-medium text-blue-600">20 Mei 2026</span>
        <h3 class="font-bold text-slate-800 dark:text-white m-0 mt-1 text-base">Jadwal Ujian Akhir Semester (UAS) Genap 2025/2026</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Ujian Akhir Semester akan diselenggarakan secara luring mulai tanggal 8 - 19 Juni 2026. Kartu ujian wajib dicetak dan dibawa.</p>
    </div>
    <div class="p-5 border-l-4 border-emerald-600 bg-slate-50 dark:bg-slate-800 rounded-r-xl">
        <span class="text-xs font-medium text-emerald-600">18 Mei 2026</span>
        <h3 class="font-bold text-slate-800 dark:text-white m-0 mt-1 text-base">Hasil Seleksi Internal Pendanaan Program Kreativitas Mahasiswa (PKM)</h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Selamat kepada 5 Tim FMIKOM yang berhasil lolos tahap pendanaan proposal PKM Dikti tahun ini. Koordinasi lanjut akan diinfokan via WhatsApp.</p>
    </div>
</div>
HTML
            ],
            [
                'title' => 'Agenda Event',
                'slug' => 'agenda-event',
                'category' => self::CATEGORY_MEDIA,
                'content' => <<<HTML
<p class="lead">Ikuti berbagai kegiatan, webinar, workshop, dan seminar yang diselenggarakan oleh FMIKOM untuk memperluas jaringan dan keahlian Anda.</p>
<div class="grid grid-cols-1 gap-4 my-8 not-prose">
    <div class="flex gap-4 p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 text-blue-600 rounded-lg flex flex-col items-center justify-center font-bold shrink-0">
            <span class="text-xs uppercase">Mei</span>
            <span class="text-xl">25</span>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 dark:text-white m-0 text-base">Seminar Nasional: Masa Depan Artificial General Intelligence (AGI)</h3>
            <p class="text-xs text-slate-500 m-0 mt-1">📍 Aula FMIKOM & Live Zoom • 09.00 WIB</p>
        </div>
    </div>
    <div class="flex gap-4 p-4 border border-slate-200 dark:border-slate-700 rounded-xl">
        <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 rounded-lg flex flex-col items-center justify-center font-bold shrink-0">
            <span class="text-xs uppercase">Jun</span>
            <span class="text-xl">02</span>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 dark:text-white m-0 text-base">Workshop UI/UX: Design System dengan Tailwind CSS & Figma</h3>
            <p class="text-xs text-slate-500 m-0 mt-1">📍 Lab RPL FMIKOM • 13.00 WIB</p>
        </div>
    </div>
</div>
HTML
            ],
            [
                'title' => 'Galeri',
                'slug' => 'galeri',
                'category' => self::CATEGORY_MEDIA,
                'content' => <<<HTML
<p class="lead">Dokumentasi visual berbagai kegiatan akademik, kemahasiswaan, riset, pengabdian masyarakat, dan kehidupan kampus di FMIKOM.</p>
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 my-8 not-prose">
    <div class="aspect-square bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden relative group">
        <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Kampus">
        <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent flex items-end p-3 opacity-0 group-hover:opacity-100 transition-opacity">
            <span class="text-xs text-white">Gedung Fakultas</span>
        </div>
    </div>
    <div class="aspect-square bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden relative group">
        <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Wisuda">
        <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent flex items-end p-3 opacity-0 group-hover:opacity-100 transition-opacity">
            <span class="text-xs text-white">Wisuda Ke-12</span>
        </div>
    </div>
    <div class="aspect-square bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden relative group">
        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Riset">
        <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent flex items-end p-3 opacity-0 group-hover:opacity-100 transition-opacity">
            <span class="text-xs text-white">Kegiatan Lab IoT</span>
        </div>
    </div>
</div>
HTML
            ],
            [
                'title' => 'Video',
                'slug' => 'video',
                'category' => self::CATEGORY_MEDIA,
                'content' => <<<HTML
<p class="lead">Video profil fakultas, liputan acara, dan kuliah umum dari para pakar IT terkemuka yang dirilis oleh FMIKOM Channel.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8 not-prose">
    <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
        <div class="aspect-video bg-slate-800 flex items-center justify-center relative">
            <div class="w-12 h-12 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center cursor-pointer text-white transition-colors">▶</div>
            <span class="absolute bottom-2 right-2 text-[10px] bg-black/60 text-white px-2 py-0.5 rounded">03:45</span>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800">
            <h4 class="font-bold text-slate-800 dark:text-white m-0 text-sm">Video Profil Resmi FMIKOM - Terdepan dalam Inovasi</h4>
        </div>
    </div>
    <div class="border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden">
        <div class="aspect-video bg-slate-800 flex items-center justify-center relative">
            <div class="w-12 h-12 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center cursor-pointer text-white transition-colors">▶</div>
            <span class="absolute bottom-2 right-2 text-[10px] bg-black/60 text-white px-2 py-0.5 rounded">12:20</span>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800">
            <h4 class="font-bold text-slate-800 dark:text-white m-0 text-sm">Dokumentasi Hackathon Nasional FMIKOM TechFest 2025</h4>
        </div>
    </div>
</div>
HTML
            ]
        ];
    }

    private function getLayananPages(): array
    {
        return [
            [
                'title' => 'Pengajuan Dokumen',
                'slug' => 'pengajuan-dokumen',
                'category' => 'Layanan',
                'content' => <<<HTML
<p class="lead">Ajukan berbagai permohonan surat keterangan aktif kuliah, rekomendasi beasiswa, pengantar magang, dan dokumen administratif lainnya secara online.</p>
<div class="my-8 overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 my-0">
        <thead class="bg-slate-50 dark:bg-slate-800/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jenis Surat</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estimasi Selesai</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Metode Pengiriman</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-sm">
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Surat Keterangan Aktif Kuliah</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">1 Hari Kerja</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">PDF Unduh Langsung</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Surat Pengantar Magang</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">2 Hari Kerja</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">PDF & Cetak Fisik</td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">Surat Keterangan Bebas Pustaka</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">3 Jam</td>
                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">Verifikasi Otomatis</td>
            </tr>
        </tbody>
    </table>
</div>
HTML
            ],
            [
                'title' => 'Konsultasi Akademik',
                'slug' => 'konsultasi-akademik',
                'category' => 'Layanan',
                'content' => <<<HTML
<p class="lead">Layanan konsultasi bimbingan akademik bersama Dosen Wali untuk merencanakan strategi studi, pemilihan mata kuliah, dan mengatasi kendala belajar.</p>
<div class="my-8 p-6 bg-linear-to-r from-emerald-50 to-teal-50 dark:from-slate-800 dark:to-slate-900 border border-emerald-100 dark:border-slate-800 rounded-2xl">
    <h3 class="mt-0 text-emerald-900 dark:text-emerald-200 text-base">Panduan Konsultasi Dosen Wali</h3>
    <ul class="mb-0 text-sm space-y-2">
        <li>Konsultasi rutin wajib dilaksanakan minimal 3 kali dalam satu semester.</li>
        <li>Jadwalkan pertemuan tatap muka atau online melalui portal sebelum masa KRS.</li>
        <li>Bawa KHS (Kartu Hasil Studi) semester sebelumnya sebagai bahan evaluasi.</li>
        <li>Mintalah persetujuan (approval) KRS daring setelah rencana studi disepakati.</li>
    </ul>
</div>
HTML
            ],
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'category' => 'Layanan',
                'content' => <<<HTML
<p class="lead">Temukan jawaban cepat atas pertanyaan-pertanyaan yang paling sering diajukan mengenai registrasi, perkuliahan, magang, dan layanan kemahasiswaan.</p>
<div class="space-y-4 my-8 not-prose">
    <div class="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl">
        <h4 class="font-bold text-slate-800 dark:text-white m-0 text-sm">Bagaimana cara mengaktifkan kembali status mahasiswa setelah cuti?</h4>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Anda dapat mengajukan surat permohonan aktif kembali ke bagian Tata Usaha dengan melampirkan bukti persetujuan cuti akademik sebelumnya paling lambat 2 minggu sebelum perkuliahan dimulai.</p>
    </div>
    <div class="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl">
        <h4 class="font-bold text-slate-800 dark:text-white m-0 text-sm">Apakah mahasiswa boleh mengambil mata kuliah magang di semester pendek?</h4>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Magang kerja industri disarankan diambil pada semester reguler (Ganjil/Genap) untuk efektivitas monitoring dan bobot SKS yang maksimal (up to 20 SKS).</p>
    </div>
    <div class="p-5 bg-slate-50 dark:bg-slate-800 rounded-xl">
        <h4 class="font-bold text-slate-800 dark:text-white m-0 text-sm">Bagaimana jika saya kehilangan berkas surat yang sudah ditandatangani Dekan?</h4>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 mb-0">Anda dapat mengajukan permohonan melalui sistem <strong>WIMS</strong> pada modul layanan surat. Surat yang disetujui akan ditandatangani secara elektronik (TTE) dan dapat langsung diunduh.</p>
    </div>
</div>
HTML
            ]
        ];
    }

    private function seedMenus(array $pageModels): void
    {
        // 3. Seed Top-Level Parent Menus
        $parentMenus = [
            'beranda' => PortalMenu::create(['title' => 'Beranda', 'url' => '/', 'order' => 1]),
            'profil'  => PortalMenu::create(['title' => 'Profil', 'url' => null, 'order' => 2]),
            'akademik'=> PortalMenu::create(['title' => 'Akademik', 'url' => null, 'order' => 3]),
            'media'   => PortalMenu::create(['title' => self::CATEGORY_MEDIA, 'url' => null, 'order' => 4]),
            'layanan' => PortalMenu::create(['title' => 'Layanan', 'url' => null, 'order' => 5]),
            'pagi'    => PortalMenu::create(['title' => 'PAGI (Portofolio)', 'url' => '/pagi', 'order' => 6]),
            'wims'    => PortalMenu::create(['title' => 'WIMS (Layanan Surat)', 'url' => '/wims', 'order' => 7]),
            'fast'    => PortalMenu::create(['title' => 'FAST (Tracer Alumni)', 'url' => '/fast', 'order' => 8]),
        ];

        // 4. Seed Child Menus
        $childMenus = [
            // Profil children
            ['parent' => 'profil', 'title' => 'Tentang FMIKOM', 'slug' => 'tentang-fmikom', 'order' => 1],
            ['parent' => 'profil', 'title' => 'Sejarah', 'slug' => 'sejarah', 'order' => 2],
            ['parent' => 'profil', 'title' => 'Visi & Misi', 'slug' => 'visi-misi', 'order' => 3],
            ['parent' => 'profil', 'title' => 'Program Studi', 'slug' => 'program-studi', 'order' => 4],
            ['parent' => 'profil', 'title' => 'Struktur Organisasi', 'slug' => 'struktur-organisasi', 'order' => 5],
            ['parent' => 'profil', 'title' => 'Dekan & Kaprodi', 'slug' => 'dekan-kaprodi', 'order' => 6],
            ['parent' => 'profil', 'title' => 'Dosen & Staff', 'slug' => 'dosen-staff', 'order' => 7],
            ['parent' => 'profil', 'title' => 'Akreditasi', 'slug' => 'akreditasi', 'order' => 8],
            ['parent' => 'profil', 'title' => 'Fasilitas', 'slug' => 'fasilitas', 'order' => 9],

            // Akademik children
            ['parent' => 'akademik', 'title' => 'Kalender Akademik', 'slug' => 'kalender-akademik', 'order' => 1],
            ['parent' => 'akademik', 'title' => 'MBKM', 'slug' => 'mbkm', 'order' => 2],
            ['parent' => 'akademik', 'title' => 'Magang', 'slug' => 'magang', 'order' => 3],
            ['parent' => 'akademik', 'title' => 'Pedoman Akademik', 'slug' => 'pedoman-akademik', 'order' => 4],
            ['parent' => 'akademik', 'title' => 'Beasiswa', 'slug' => 'beasiswa', 'order' => 5],
            ['parent' => 'akademik', 'title' => 'Download Dokumen', 'slug' => 'download-dokumen', 'order' => 6],

            // Berita & Media children
            ['parent' => 'media', 'title' => 'Berita', 'slug' => 'berita', 'url' => '/berita', 'order' => 1],
            ['parent' => 'media', 'title' => 'Pengumuman', 'slug' => 'pengumuman', 'order' => 2],
            ['parent' => 'media', 'title' => 'Agenda Event', 'slug' => 'agenda-event', 'order' => 3],
            ['parent' => 'media', 'title' => 'Galeri', 'slug' => 'galeri', 'order' => 4],
            ['parent' => 'media', 'title' => 'Video', 'slug' => 'video', 'order' => 5],

            // Layanan children
            ['parent' => 'layanan', 'title' => 'Pengajuan Dokumen', 'slug' => 'pengajuan-dokumen', 'order' => 1],
            ['parent' => 'layanan', 'title' => 'Konsultasi Akademik', 'slug' => 'konsultasi-akademik', 'order' => 2],
            ['parent' => 'layanan', 'title' => 'FAQ', 'slug' => 'faq', 'order' => 3],
        ];

        foreach ($childMenus as $cm) {
            $parentId = $parentMenus[$cm['parent']]->id;
            $pageId = isset($cm['slug']) && isset($pageModels[$cm['slug']]) ? $pageModels[$cm['slug']]->id : null;
            $url = $cm['url'] ?? null;

            PortalMenu::create([
                'title' => $cm['title'],
                'url' => $url,
                'portal_page_id' => $pageId,
                'parent_id' => $parentId,
                'order' => $cm['order']
            ]);
        }
    }

    private function seedPosts(User $user): void
    {
        $posts = [
            [
                'title' => 'Pendaftaran Magang Semester Ganjil 2026/2027 Dibuka!',
                'slug' => 'pendaftaran-magang-semester-ganjil-2026-2027-dibuka',
                'excerpt' => 'Pendaftaran program magang FMIKOM untuk semester ganjil kini telah resmi dibuka. Segera daftarkan diri Anda dan pilih mitra industri terbaik.',
                'content' => 'Silakan login dan akses menu WIMS untuk melakukan pendaftaran magang.',
                'thumbnail' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=2671&auto=format&fit=crop',
                'is_published' => true,
                'status' => 'published',
                'published_at' => now(),
                'user_id' => $user->id,
            ],
            [
                'title' => 'Workshop UI/UX Design Berbasis Tailwind CSS & Vue 3',
                'slug' => 'workshop-uiux-design-berbasis-tailwind-css-vue-3',
                'excerpt' => 'Ikuti workshop intensif perancangan antarmuka pengguna modern menggunakan ekosistem terbaru Vue dan Tailwind.',
                'content' => 'Workshop akan diadakan pada akhir pekan ini di aula utama FMIKOM.',
                'thumbnail' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?q=80&w=2670&auto=format&fit=crop',
                'is_published' => true,
                'status' => 'published',
                'published_at' => now(),
                'user_id' => $user->id,
            ]
        ];

        foreach ($posts as $post) {
            PortalPost::create($post);
        }
    }

    private function seedPortfolios(User $user): void
    {
        $portfolios = [
            [
                'title' => 'Redesign Dashboard Akademik Kampus',
                'cover_image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?q=80&w=2670&auto=format&fit=crop',
                'is_published' => true,
                'status' => 'active',
                'description' => 'Sebuah konsep desain ulang dashboard akademik mahasiswa yang berfokus pada kemudahan akses informasi nilai dan administrasi.',
                'category' => 'Design & UI/UX',
                'content' => json_encode([['type' => 'paragraph', 'data' => ['text' => 'Redesign project.']]]),
                'user_id' => $user->id,
            ],
            [
                'title' => 'Aplikasi Monitoring Suhu Server berbasis IoT',
                'cover_image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=2670&auto=format&fit=crop',
                'is_published' => true,
                'status' => 'active',
                'description' => 'Sistem real-time monitoring suhu dan kelembaban ruang server fakultas menggunakan mikrokontroler ESP32 dan protokol MQTT.',
                'category' => 'IoT & Hardware',
                'content' => json_encode([['type' => 'paragraph', 'data' => ['text' => 'IoT project.']]]),
                'user_id' => $user->id,
            ]
        ];

        foreach ($portfolios as $portfolio) {
            PagiWork::create($portfolio);
        }
    }

    private function seedAcademicEvents(): void
    {
        $currentYear = date('Y');
        $currentMonth = date('m');

        $academicEvents = [
            [
                'title' => 'Masa Pengisian KRS Semester Ganjil',
                'description' => 'Masa pengisian Kartu Rencana Studi (KRS) daring oleh seluruh mahasiswa aktif FMIKOM.',
                'start_date' => "$currentYear-$currentMonth-02",
                'end_date' => "$currentYear-$currentMonth-08",
                'category' => 'registrasi',
                'color' => 'amber'
            ],
            [
                'title' => 'Kuliah Umum: Tren Web Development 2026',
                'description' => 'Kuliah perdana pembuka semester ganjil bersama pakar industri dari Google & GoTo.',
                'start_date' => "$currentYear-$currentMonth-12",
                'end_date' => "$currentYear-$currentMonth-12",
                'category' => 'kegiatan',
                'color' => 'green'
            ],
            [
                'title' => 'Masa Perkuliahan Efektif Semester Ganjil',
                'description' => 'Masa perkuliahan tatap muka dan daring terjadwal.',
                'start_date' => "$currentYear-$currentMonth-15",
                'end_date' => date('Y-m-d', strtotime('+3 months')),
                'category' => 'akademik',
                'color' => 'blue'
            ],
            [
                'title' => 'Libur Hari Lahir Pancasila',
                'description' => 'Hari Libur Nasional.',
                'start_date' => "$currentYear-06-01",
                'end_date' => "$currentYear-06-01",
                'category' => 'libur',
                'color' => 'red'
            ],
            [
                'title' => 'Ujian Tengah Semester (UTS) Ganjil',
                'description' => 'Pelaksanaan asesmen tengah semester untuk menguji capaian belajar mahasiswa.',
                'start_date' => date('Y-m-d', strtotime('+1.5 months')),
                'end_date' => date('Y-m-d', strtotime('+1.5 months + 7 days')),
                'category' => 'ujian',
                'color' => 'purple'
            ]
        ];

        foreach ($academicEvents as $ac) {
            PortalAcademicCalendar::create($ac);
        }
    }

    private function seedCampusEvents(): void
    {
        $eventsData = [
            [
                'title' => 'Seminar Nasional: Perkembangan AI Agentic di Era Industri 5.0',
                'slug' => 'seminar-nasional-perkembangan-ai-agentic',
                'description' => 'Bergabunglah dalam seminar nasional yang mengupas tuntas masa depan agen AI otonom, implikasi etis, dan pemanfaatannya dalam ekosistem korporasi modern.',
                'location' => 'Aula Utama Gedung Adisucipto FMIKOM',
                'start_time' => date(self::DATE_TIME_FORMAT, strtotime('+5 days 09:00:00')),
                'end_time' => date(self::DATE_TIME_FORMAT, strtotime('+5 days 13:00:00')),
                'registration_link' => 'https://fmikom.ac.id/register-ai-seminar',
                'status' => 'published',
                'thumbnail' => 'https://images.unsplash.com/photo-1591453089816-0fbb971b454c?q=80&w=2670&auto=format&fit=crop'
            ],
            [
                'title' => 'Hackathon FMIKOM 2026: Green Technology Solutions',
                'slug' => 'hackathon-fmikom-2026-green-tech',
                'description' => 'Kompetisi coding 48 jam tingkat nasional untuk merancang solusi perangkat lunak ramah lingkungan guna mempercepat dekarbonisasi.',
                'location' => 'Laboratorium Rekayasa Perangkat Lunak Gedung B',
                'start_time' => date(self::DATE_TIME_FORMAT, strtotime('+15 days 08:00:00')),
                'end_time' => date(self::DATE_TIME_FORMAT, strtotime('+17 days 18:00:00')),
                'registration_link' => 'https://fmikom.ac.id/green-hackathon',
                'status' => 'published',
                'thumbnail' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?q=80&w=2670&auto=format&fit=crop'
            ],
            [
                'title' => 'Workshop Vue 3 & Laravel Inertia untuk Pemula',
                'slug' => 'workshop-vue-3-laravel-inertia',
                'description' => 'Pelajari cara tercepat membangun aplikasi web modern satu halaman (SPA) menggunakan arsitektur monolitik modern dari awal.',
                'location' => 'Lab Komputer 3',
                'start_time' => date(self::DATE_TIME_FORMAT, strtotime('+3 days 13:00:00')),
                'end_time' => date(self::DATE_TIME_FORMAT, strtotime('+3 days 16:30:00')),
                'registration_link' => null,
                'status' => 'draft',
                'thumbnail' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?q=80&w=2670&auto=format&fit=crop'
            ]
        ];

        foreach ($eventsData as $ed) {
            PortalEvent::create($ed);
        }
    }
}
