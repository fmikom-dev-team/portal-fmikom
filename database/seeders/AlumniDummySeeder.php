<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AlumniDummySeeder extends Seeder
{
    /**
     * Seed 150 realistic alumni with profiles, career history, employment/education data.
     * All data is designed to populate the WebGIS Map properly.
     */
    public function run(): void
    {
        $this->command->info('🎓 Seeding alumni dummy data...');

        // ─── Prerequisites ──────────────────────────────────
        $kotaRecords = DB::table('kota')->get();
        if ($kotaRecords->isEmpty()) {
            $this->command->error('❌ Kota table is empty! Run ProvinsiSeeder & KotaSeeder first.');
            return;
        }

        $prodiIds = DB::table('program_studis')->pluck('id')->toArray();
        if (empty($prodiIds)) {
            $this->command->error('❌ Program studi table is empty! Run FakultasProdiSeeder first.');
            return;
        }

        // ─── Reference Data ─────────────────────────────────
        $namaDepanLaki = [
            'Ahmad', 'Budi', 'Cahyo', 'Dimas', 'Eka', 'Fajar', 'Gilang', 'Hendra',
            'Irfan', 'Joko', 'Kevin', 'Lukman', 'Muhammad', 'Naufal', 'Oscar',
            'Prasetyo', 'Rafi', 'Satria', 'Teguh', 'Umar', 'Vino', 'Wahyu',
            'Yoga', 'Zulfikar', 'Andi', 'Bayu', 'Dedi', 'Farhan', 'Galih',
            'Rizky', 'Arief', 'Dwi', 'Febri', 'Hadi', 'Ilham', 'Kurniawan',
        ];
        $namaDepanPerempuan = [
            'Ayu', 'Bunga', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hana',
            'Indah', 'Julia', 'Kartika', 'Lestari', 'Maya', 'Nadia', 'Okta',
            'Putri', 'Ratna', 'Sari', 'Tika', 'Utami', 'Vina', 'Wulan',
            'Yuni', 'Zahra', 'Annisa', 'Bella', 'Diana', 'Fania', 'Galuh',
            'Rina', 'Sinta', 'Dina', 'Farah', 'Hesti', 'Intan', 'Kirana',
        ];
        $namaBelakang = [
            'Pratama', 'Saputra', 'Hidayat', 'Nugraha', 'Kurniawan', 'Ramadhan',
            'Permana', 'Kusuma', 'Wijaya', 'Santoso', 'Purnama', 'Wicaksono',
            'Setiawan', 'Firmansyah', 'Hakim', 'Mahendra', 'Putra', 'Suryadi',
            'Anggara', 'Utomo', 'Prabowo', 'Hartono', 'Susanto', 'Rahayu',
            'Laksana', 'Dharma', 'Pranata', 'Adinata', 'Yudhistira', 'Wibowo',
        ];

        $sektorIndustri = [
            'Teknologi Informasi', 'Perbankan & Keuangan', 'E-Commerce',
            'Telekomunikasi', 'Kesehatan', 'Manufaktur', 'Logistik',
            'Media & Hiburan', 'Pendidikan', 'Energi & Pertambangan',
            'Konsultan', 'Pemerintahan', 'Startup', 'Ritel',
            'Asuransi', 'Properti', 'Otomotif', 'Agritech',
        ];

        $perusahaan = [
            'Teknologi Informasi' => [
                ['nama' => 'PT Telkom Indonesia', 'jabatan' => ['Software Engineer', 'Data Analyst', 'DevOps Engineer', 'System Administrator']],
                ['nama' => 'PT Tokopedia', 'jabatan' => ['Backend Developer', 'Frontend Developer', 'Product Manager', 'QA Engineer']],
                ['nama' => 'GoTo Group', 'jabatan' => ['Mobile Developer', 'Data Scientist', 'Tech Lead', 'Site Reliability Engineer']],
                ['nama' => 'Traveloka', 'jabatan' => ['Full Stack Developer', 'Machine Learning Engineer', 'Cloud Architect']],
                ['nama' => 'Bukalapak', 'jabatan' => ['Software Developer', 'UI/UX Designer', 'Security Engineer']],
                ['nama' => 'PT Astra Graphia', 'jabatan' => ['IT Consultant', 'Project Manager', 'System Analyst']],
            ],
            'Perbankan & Keuangan' => [
                ['nama' => 'Bank Central Asia', 'jabatan' => ['IT Officer', 'Data Engineer', 'Application Developer']],
                ['nama' => 'Bank Mandiri', 'jabatan' => ['Digital Banking Analyst', 'IT Risk Officer', 'System Developer']],
                ['nama' => 'Bank Rakyat Indonesia', 'jabatan' => ['Software Developer', 'Database Administrator']],
                ['nama' => 'OVO', 'jabatan' => ['Backend Engineer', 'Mobile Developer', 'Fraud Analyst']],
            ],
            'E-Commerce' => [
                ['nama' => 'Shopee Indonesia', 'jabatan' => ['Software Engineer', 'Data Analyst', 'Product Designer']],
                ['nama' => 'Blibli', 'jabatan' => ['Full Stack Developer', 'QA Automation', 'Scrum Master']],
                ['nama' => 'Lazada Indonesia', 'jabatan' => ['Backend Developer', 'DevOps Engineer']],
            ],
            'Telekomunikasi' => [
                ['nama' => 'PT Indosat Ooredoo', 'jabatan' => ['Network Engineer', 'System Analyst', 'IT Support Lead']],
                ['nama' => 'PT XL Axiata', 'jabatan' => ['Data Engineer', 'Cloud Engineer', 'IT Manager']],
            ],
            'Kesehatan' => [
                ['nama' => 'Halodoc', 'jabatan' => ['Software Engineer', 'Product Manager', 'Data Scientist']],
                ['nama' => 'Alodokter', 'jabatan' => ['Mobile Developer', 'Backend Engineer']],
            ],
            'Konsultan' => [
                ['nama' => 'Accenture Indonesia', 'jabatan' => ['Technology Consultant', 'Application Developer', 'Business Analyst']],
                ['nama' => 'Deloitte Indonesia', 'jabatan' => ['IT Auditor', 'Risk Advisory Analyst', 'Cyber Security Consultant']],
            ],
            'Startup' => [
                ['nama' => 'Ruangguru', 'jabatan' => ['Software Engineer', 'Content Developer', 'Product Manager']],
                ['nama' => 'Xendit', 'jabatan' => ['Backend Engineer', 'DevOps Engineer', 'Integration Engineer']],
                ['nama' => 'Stockbit', 'jabatan' => ['Frontend Developer', 'Mobile Developer']],
            ],
            'Pemerintahan' => [
                ['nama' => 'Kementerian Kominfo', 'jabatan' => ['Analis Sistem', 'Pranata Komputer', 'Programmer']],
                ['nama' => 'BSSN', 'jabatan' => ['Cyber Security Analyst', 'Network Security Engineer']],
                ['nama' => 'BPS', 'jabatan' => ['Data Analyst', 'Statistisi', 'Programmer']],
            ],
        ];

        // Fill remaining sectors with generic companies
        foreach ($sektorIndustri as $sektor) {
            if (!isset($perusahaan[$sektor])) {
                $perusahaan[$sektor] = [
                    ['nama' => "PT {$sektor} Nusantara", 'jabatan' => ['Staff IT', 'System Analyst', 'Developer']],
                    ['nama' => "CV {$sektor} Digital", 'jabatan' => ['Programmer', 'IT Support', 'Web Developer']],
                ];
            }
        }

        $universitas = [
            ['nama' => 'Universitas Indonesia', 'prodi' => ['Ilmu Komputer', 'Sistem Informasi', 'Data Science'], 'jenjang' => 'S2'],
            ['nama' => 'Institut Teknologi Bandung', 'prodi' => ['Informatika', 'Teknik Elektro', 'Sains Data'], 'jenjang' => 'S2'],
            ['nama' => 'Universitas Gadjah Mada', 'prodi' => ['Computer Science', 'Information System'], 'jenjang' => 'S2'],
            ['nama' => 'ITS Surabaya', 'prodi' => ['Teknik Informatika', 'Sistem Informasi'], 'jenjang' => 'S2'],
            ['nama' => 'Binus University', 'prodi' => ['Computer Science', 'Information Technology'], 'jenjang' => 'S2'],
            ['nama' => 'National University of Singapore', 'prodi' => ['Computer Science', 'Data Science'], 'jenjang' => 'S2'],
            ['nama' => 'Monash University', 'prodi' => ['Information Technology', 'AI & ML'], 'jenjang' => 'S2'],
        ];

        $wirausahaUsaha = [
            ['nama' => 'CV Teknologi Kreatif', 'jabatan' => 'CEO & Founder', 'sektor' => 'Startup'],
            ['nama' => 'PT Digital Inovasi', 'jabatan' => 'Co-Founder', 'sektor' => 'Teknologi Informasi'],
            ['nama' => 'Warung Kopi Nusantara', 'jabatan' => 'Owner', 'sektor' => 'Ritel'],
            ['nama' => 'Studio Design Pixel', 'jabatan' => 'Creative Director', 'sektor' => 'Media & Hiburan'],
            ['nama' => 'Toko Online Berkah', 'jabatan' => 'Founder', 'sektor' => 'E-Commerce'],
            ['nama' => 'CV Solusi Data', 'jabatan' => 'Managing Director', 'sektor' => 'Konsultan'],
            ['nama' => 'Farm Tech Solutions', 'jabatan' => 'CEO', 'sektor' => 'Agritech'],
            ['nama' => 'Jasa IT Mandiri', 'jabatan' => 'Owner & Lead Developer', 'sektor' => 'Teknologi Informasi'],
        ];

        // Key cities with real coordinates for realistic distribution
        $kotaUtama = [
            // Jawa (heavy concentration)
            ['prov' => 'DKI JAKARTA', 'kotas' => ['KOTA JAKARTA SELATAN', 'KOTA JAKARTA PUSAT', 'KOTA JAKARTA BARAT', 'KOTA JAKARTA TIMUR', 'KOTA JAKARTA UTARA']],
            ['prov' => 'JAWA BARAT', 'kotas' => ['KOTA BANDUNG', 'KABUPATEN BANDUNG', 'KOTA BEKASI', 'KOTA BOGOR', 'KOTA DEPOK', 'KOTA CIMAHI', 'KABUPATEN KARAWANG']],
            ['prov' => 'JAWA TENGAH', 'kotas' => ['KOTA SEMARANG', 'KOTA SURAKARTA', 'KABUPATEN BANYUMAS']],
            ['prov' => 'JAWA TIMUR', 'kotas' => ['KOTA SURABAYA', 'KOTA MALANG', 'KABUPATEN SIDOARJO']],
            ['prov' => 'BANTEN', 'kotas' => ['KOTA TANGERANG', 'KOTA TANGERANG SELATAN', 'KOTA SERANG']],
            ['prov' => 'DI YOGYAKARTA', 'kotas' => ['KOTA YOGYAKARTA', 'KABUPATEN SLEMAN']],
            // Sumatera
            ['prov' => 'SUMATERA UTARA', 'kotas' => ['KOTA MEDAN', 'KOTA BINJAI']],
            ['prov' => 'SUMATERA BARAT', 'kotas' => ['KOTA PADANG', 'KOTA BUKITTINGGI']],
            ['prov' => 'SUMATERA SELATAN', 'kotas' => ['KOTA PALEMBANG']],
            ['prov' => 'LAMPUNG', 'kotas' => ['KOTA BANDAR LAMPUNG']],
            ['prov' => 'RIAU', 'kotas' => ['KOTA PEKANBARU']],
            // Kalimantan
            ['prov' => 'KALIMANTAN TIMUR', 'kotas' => ['KOTA BALIKPAPAN', 'KOTA SAMARINDA']],
            ['prov' => 'KALIMANTAN SELATAN', 'kotas' => ['KOTA BANJARMASIN']],
            // Sulawesi
            ['prov' => 'SULAWESI SELATAN', 'kotas' => ['KOTA MAKASSAR']],
            // Bali & NTB
            ['prov' => 'BALI', 'kotas' => ['KOTA DENPASAR', 'KABUPATEN BADUNG']],
        ];

        // Build lookup: kota name -> record
        $kotaLookup = [];
        foreach ($kotaRecords as $k) {
            $kotaLookup[strtoupper($k->name)] = $k;
        }

        // Build weighted kota pool (Jawa gets more weight)
        $kotaPool = [];
        $weights = [
            'DKI JAKARTA' => 8, 'JAWA BARAT' => 7, 'JAWA TENGAH' => 3,
            'JAWA TIMUR' => 4, 'BANTEN' => 4, 'DI YOGYAKARTA' => 3,
        ];
        foreach ($kotaUtama as $group) {
            $w = $weights[$group['prov']] ?? 1;
            foreach ($group['kotas'] as $kotaName) {
                $key = strtoupper($kotaName);
                if (isset($kotaLookup[$key])) {
                    for ($i = 0; $i < $w; $i++) {
                        $kotaPool[] = $kotaLookup[$key];
                    }
                }
            }
        }

        if (empty($kotaPool)) {
            // Fallback: use any kota with lat/lng
            $kotaPool = $kotaRecords->filter(fn($k) => $k->latitude && $k->longitude)->values()->all();
        }

        if (empty($kotaPool)) {
            $this->command->error('❌ No kota with coordinates found!');
            return;
        }

        $this->command->info('📍 ' . count($kotaPool) . ' kota entries in pool');

        // ─── Generate Alumni ────────────────────────────────
        $totalAlumni = 150;
        $now = Carbon::now();
        $passwordHash = Hash::make('password123');

        // Status distribution: 55% bekerja, 15% wirausaha, 15% lanjut_studi, 15% mencari_kerja
        $statusDistribution = array_merge(
            array_fill(0, 82, 'bekerja'),
            array_fill(0, 23, 'wirausaha'),
            array_fill(0, 23, 'lanjut_studi'),
            array_fill(0, 22, 'mencari_kerja'),
        );
        shuffle($statusDistribution);

        $created = 0;

        DB::beginTransaction();
        try {
            for ($i = 0; $i < $totalAlumni; $i++) {
                $gender = fake()->randomElement(['L', 'P']);
                $firstName = $gender === 'L'
                    ? fake()->randomElement($namaDepanLaki)
                    : fake()->randomElement($namaDepanPerempuan);
                $lastName = fake()->randomElement($namaBelakang);
                $fullName = "{$firstName} {$lastName}";

                $angkatan = fake()->randomElement([2016, 2017, 2018, 2019, 2020, 2021, 2022]);
                $prodiId = fake()->randomElement($prodiIds);
                $nim = $angkatan . str_pad($prodiId, 2, '0', STR_PAD_LEFT) . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

                $status = $statusDistribution[$i] ?? 'bekerja';

                // Pick a random kota for home
                $homeKota = fake()->randomElement($kotaPool);
                $homeLat = (float) $homeKota->latitude + (mt_rand(-500, 500) / 10000);
                $homeLng = (float) $homeKota->longitude + (mt_rand(-500, 500) / 10000);

                // Pick a kota for work/study (can be different from home)
                $workKota = fake()->randomElement($kotaPool);
                $workLat = (float) $workKota->latitude + (mt_rand(-500, 500) / 10000);
                $workLng = (float) $workKota->longitude + (mt_rand(-500, 500) / 10000);

                // ── 1. Create User ──
                $userId = DB::table('users')->insertGetId([
                    'name' => $fullName,
                    'email' => Str::slug($firstName . '.' . $lastName . $i, '.') . '@alumni.fmikom.ac.id',
                    'user_type' => 'alumni',
                    'nomor_induk' => $nim,
                    'tanggal_lahir' => Carbon::create($angkatan - 18, mt_rand(1, 12), mt_rand(1, 28)),
                    'status_approval' => 'approved',
                    'tahun_lulus' => $angkatan + 4,
                    'email_verified_at' => $now,
                    'password' => $passwordHash,
                    'program_studi_id' => $prodiId,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // ── 2. Create Profil Alumni ──
                $profilId = DB::table('profil_alumnis')->insertGetId([
                    'user_id' => $userId,
                    'jenis_kelamin' => $gender,
                    'angkatan' => $angkatan,
                    'provinsi_id' => $homeKota->provinsi_id,
                    'kota_id' => $homeKota->id,
                    'alamat_rumah' => "Jl. " . fake()->randomElement(['Merdeka', 'Sudirman', 'Gatot Subroto', 'Ahmad Yani', 'Diponegoro', 'Pahlawan', 'Veteran', 'Kartini']) . " No. " . mt_rand(1, 200),
                    'latitude_rumah' => $homeLat,
                    'longitude_rumah' => $homeLng,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // ── 3. Create Career History + Detail ──
                if ($status === 'bekerja') {
                    $sektor = fake()->randomElement($sektorIndustri);
                    $companies = $perusahaan[$sektor];
                    $company = fake()->randomElement($companies);
                    $jabatan = fake()->randomElement($company['jabatan']);
                    $tahunMulai = $angkatan + 4 + mt_rand(0, 1);

                    $careerId = DB::table('career_history')->insertGetId([
                        'profil_alumni_id' => $profilId,
                        'type' => 'employment',
                        'status' => 'bekerja',
                        'tahun_mulai' => $tahunMulai,
                        'tanggal_mulai' => Carbon::create($tahunMulai, mt_rand(1, 12), 1),
                        'is_current' => true,
                        'provinsi_id' => $workKota->provinsi_id,
                        'kota_id' => $workKota->id,
                        'latitude' => $workLat,
                        'longitude' => $workLng,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('employment')->insert([
                        'career_history_id' => $careerId,
                        'nama_perusahaan' => $company['nama'],
                        'jabatan' => $jabatan,
                        'sektor_industri' => $sektor,
                        'alamat_perusahaan' => $workKota->name,
                        'gaji_min' => fake()->randomElement([5000000, 7000000, 10000000, 12000000, 15000000]),
                        'gaji_max' => fake()->randomElement([8000000, 12000000, 18000000, 25000000, 35000000]),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                } elseif ($status === 'wirausaha') {
                    $usaha = fake()->randomElement($wirausahaUsaha);
                    $tahunMulai = $angkatan + 4 + mt_rand(0, 2);

                    $careerId = DB::table('career_history')->insertGetId([
                        'profil_alumni_id' => $profilId,
                        'type' => 'employment',
                        'status' => 'wirausaha',
                        'tahun_mulai' => $tahunMulai,
                        'tanggal_mulai' => Carbon::create($tahunMulai, mt_rand(1, 12), 1),
                        'is_current' => true,
                        'provinsi_id' => $workKota->provinsi_id,
                        'kota_id' => $workKota->id,
                        'latitude' => $workLat,
                        'longitude' => $workLng,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('employment')->insert([
                        'career_history_id' => $careerId,
                        'nama_perusahaan' => $usaha['nama'],
                        'jabatan' => $usaha['jabatan'],
                        'sektor_industri' => $usaha['sektor'],
                        'alamat_perusahaan' => $workKota->name,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                } elseif ($status === 'lanjut_studi') {
                    $univ = fake()->randomElement($universitas);
                    $prodiLanjut = fake()->randomElement($univ['prodi']);
                    $tahunMulai = $angkatan + 4 + mt_rand(0, 1);

                    $careerId = DB::table('career_history')->insertGetId([
                        'profil_alumni_id' => $profilId,
                        'type' => 'education',
                        'status' => 'lanjut_studi',
                        'tahun_mulai' => $tahunMulai,
                        'tanggal_mulai' => Carbon::create($tahunMulai, mt_rand(7, 9), 1),
                        'is_current' => true,
                        'provinsi_id' => $workKota->provinsi_id,
                        'kota_id' => $workKota->id,
                        'latitude' => $workLat,
                        'longitude' => $workLng,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('education')->insert([
                        'career_history_id' => $careerId,
                        'nama_universitas' => $univ['nama'],
                        'program_studi_lanjutan' => $prodiLanjut,
                        'jenjang_pendidikan' => $univ['jenjang'],
                        'sumber_biaya' => fake()->randomElement(['Mandiri', 'Beasiswa LPDP', 'Beasiswa Unggulan', 'Beasiswa Kampus', 'Sponsor Perusahaan']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                } else { // mencari_kerja
                    DB::table('career_history')->insert([
                        'profil_alumni_id' => $profilId,
                        'type' => 'unemployment',
                        'status' => 'mencari_kerja',
                        'tahun_mulai' => $angkatan + 4,
                        'tanggal_mulai' => Carbon::create($angkatan + 4, mt_rand(6, 12), 1),
                        'is_current' => true,
                        'provinsi_id' => $homeKota->provinsi_id,
                        'kota_id' => $homeKota->id,
                        'latitude' => null, // MapController uses profil.latitude_rumah for this
                        'longitude' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $created++;
            }

            DB::commit();
            $this->command->info("✅ Berhasil membuat {$created} alumni dummy!");
            $this->command->info("   📊 Distribusi: ~82 Bekerja, ~23 Wirausaha, ~23 Lanjut Studi, ~22 Mencari Kerja");
            $this->command->info("   🔑 Password semua alumni: password123");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error: " . $e->getMessage());
            throw $e;
        }
    }
}
