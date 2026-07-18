<?php

namespace App\Modules\Pagi\Controllers\Concerns;

use App\Models\Pagi\PagiReport;
use App\Models\Pagi\PagiWarning;
use App\Models\Pagi\PagiWork;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

trait HasAdminDashboardHelpers
{
    protected static string $DEFAULT_STUDENT_EMAIL = 'student@fmikom.ac.id';

    protected static string $DEFAULT_REPORTER_EMAIL = 'reporter@fmikom.ac.id';

    /**
     * Auto-seed real portfolio & moderation data linked together in the database
     */
    protected function seedPagiDemoData(): void
    {
        $studentsData = [
            [
                'name' => 'Sarah Aulia',
                'email' => 'sarah@student.fmikom.ac.id',
                'nim' => '2021010001',
                'prodi' => 1,
                'role_title' => 'UI/UX Designer',
                'bio' => 'Senang mendesain interface aplikasi mobile dan web yang fungsional dan indah.',
            ],
            [
                'name' => 'Naufal Dzaky',
                'email' => 'naufal@student.fmikom.ac.id',
                'nim' => '2021010002',
                'prodi' => 1,
                'role_title' => 'Frontend Developer',
                'bio' => 'Belajar React, Vue, dan ekosistem JS modern.',
            ],
            [
                'name' => 'Dimas Wirawan',
                'email' => 'dimas@student.fmikom.ac.id',
                'nim' => '2022010015',
                'prodi' => 2,
                'role_title' => 'Graphic Designer',
                'bio' => 'Spesialis branding, tipografi, dan ilustrasi digital.',
            ],
            [
                'name' => 'Rizki Design',
                'email' => 'rizki@student.fmikom.ac.id',
                'nim' => '2022010030',
                'prodi' => 2,
                'role_title' => 'Product Designer',
                'bio' => 'Fokus pada riset pengguna dan wireframing produk digital.',
            ],
            [
                'name' => 'Johan Triwibowo',
                'email' => 'johan@student.fmikom.ac.id',
                'nim' => '2020010045',
                'prodi' => 1,
                'role_title' => '3D Artist',
                'bio' => 'Pembuat visualisasi 3D interior dan aset game.',
            ],
            [
                'name' => 'Fitria Nur',
                'email' => 'fitria@student.fmikom.ac.id',
                'nim' => '2023010008',
                'prodi' => 1,
                'role_title' => 'Mobile Developer',
                'bio' => 'Senang memprogram aplikasi mobile native untuk iOS dan Android.',
            ],
        ];

        $users = [];
        foreach ($studentsData as $data) {
            $users[] = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                    'user_type' => 'mahasiswa',
                    'role_title' => $data['role_title'],
                    'bio' => $data['bio'],
                    'nomor_induk' => $data['nim'],
                    'program_studi_id' => $data['prodi'],
                    'location' => 'Purwokerto, Indonesia',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Seed some mitra partner accounts
        $mitrasData = [
            [
                'name' => 'PT Telkom Indonesia',
                'email' => 'hr@telkom.co.id',
                'pic' => 'Budi Santoso',
            ],
            [
                'name' => 'Gojek Tokopedia (GoTo)',
                'email' => 'partners@goto.com',
                'pic' => 'Andi Wijaya',
            ],
        ];
        foreach ($mitrasData as $mData) {
            User::updateOrCreate(
                ['email' => $mData['email']],
                [
                    'name' => $mData['name'],
                    'password' => Hash::make('password123'),
                    'user_type' => 'mitra',
                    'metadata' => ['pic' => $mData['pic']],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $admin = User::query()->where('user_type', '=', 'super_admin', 'and')->first(['*']) ?: User::query()->first(['*']);

        // 2. Create Works
        $worksData = [
            [
                'title' => 'Perancangan UI Aplikasi EduLearn',
                'user_email' => 'sarah@student.fmikom.ac.id',
                'description' => 'Eksplorasi UI/UX aplikasi pembelajaran online interaktif untuk mahasiswa FMIKOM.',
                'category' => 'Design & UI/UX',
                'views_count' => 1200,
                'is_published' => true,
                'status' => 'active',
                'cover_image' => 'https://images.unsplash.com/photo-1586717791821-3f44a563fa4c?w=400&auto=format&fit=crop',
            ],
            [
                'title' => 'Eksplorasi Tipografi Eksperimental',
                'user_email' => 'dimas@student.fmikom.ac.id',
                'description' => 'Konsep tipografi poster yang menggabungkan elemen retro dan modern.',
                'category' => 'Graphic Design',
                'views_count' => 980,
                'is_published' => true,
                'status' => 'active',
                'cover_image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=400&auto=format&fit=crop',
            ],
            [
                'title' => 'Prototype Aplikasi Traveling',
                'user_email' => 'rizki@student.fmikom.ac.id',
                'description' => 'Aplikasi pencarian rute wisata lokal di Purwokerto.',
                'category' => 'Product Design',
                'views_count' => 320,
                'is_published' => true,
                'status' => 'hidden',
                'cover_image' => 'https://images.unsplash.com/photo-1555774698-0b77e0d5fac6?w=400&auto=format&fit=crop',
            ],
            [
                'title' => '3D Visualisasi Interior Cafe',
                'user_email' => 'johan@student.fmikom.ac.id',
                'description' => 'Rendering interior cafe minimalis menggunakan Blender.',
                'category' => '3D Modeling',
                'views_count' => 870,
                'is_published' => true,
                'status' => 'review',
                'cover_image' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=400&auto=format&fit=crop',
            ],
            [
                'title' => 'Desain Poster Event Musik',
                'user_email' => 'naufal@student.fmikom.ac.id',
                'description' => 'Poster promosi konser musik kampus tahunan.',
                'category' => 'Graphic Design',
                'views_count' => 120,
                'is_published' => true,
                'status' => 'active',
                'cover_image' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=400&auto=format&fit=crop',
            ],
        ];

        $ports = [];
        foreach ($worksData as $pData) {
            $user = User::query()->where('email', '=', $pData['user_email'], 'and')->first(['*']);
            if ($user) {
                $ports[] = PagiWork::create([
                    'user_id' => $user->id,
                    'title' => $pData['title'],
                    'description' => $pData['description'],
                    'category' => $pData['category'],
                    'views_count' => $pData['views_count'],
                    'is_published' => $pData['is_published'],
                    'status' => $pData['status'],
                    'cover_image' => $pData['cover_image'] ?? null,
                    'content' => [['type' => 'paragraph', 'data' => ['text' => $pData['description']]]],
                ]);
            }
        }

        // 3. Create Reports
        if (count($ports) >= 4) {
            PagiReport::create([
                'work_id' => $ports[2]->id,
                'reporter_id' => $users[1]->id,
                'reason' => 'copyright_violation',
                'description' => 'Terdapat indikasi kemiripan 90% dengan karya desain di Dribbble.',
                'status' => 'pending',
            ]);

            PagiReport::create([
                'work_id' => $ports[3]->id,
                'reporter_id' => $users[0]->id,
                'reason' => 'copyright_violation',
                'description' => 'Aset model 3D menggunakan milik berbayar tanpa lisensi komersial.',
                'status' => 'pending',
            ]);
        }

        // 4. Create Warnings
        if (count($users) >= 3 && $admin) {
            PagiWarning::create([
                'user_id' => $users[2]->id,
                'issued_by' => $admin->id,
                'severity' => 'medium',
                'type' => 'inappropriate_content',
                'reason' => 'Menggunakan gambar beresolusi rendah dan mengandung watermark berbayar.',
                'is_active' => true,
                'expires_at' => now()->addDays(30),
            ]);
        }
    }

    protected function getStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : asset('storage/'.$path);
    }

    protected function getReportReasonLabel(?string $reason): string
    {
        return match ($reason) {
            'copyright_violation' => 'Pelanggaran Hak Cipta',
            'inappropriate_content' => 'Konten Tidak Pantas',
            'spam' => 'Spam',
            'harassment' => 'Pelecehan',
            'misinformation' => 'Misinformasi',
            default => 'Lainnya',
        };
    }

    protected function formatChartLabel(Carbon $date, int $offsetFromEnd, int $totalDays): string
    {
        if ($totalDays <= 30) {
            return $date->translatedFormat('d M');
        }

        // For 90-day range: only label every 10th day and the first/last
        $isLabelDay = ($offsetFromEnd % 10 === 0)
            || $offsetFromEnd === $totalDays - 1
            || $offsetFromEnd === 0;

        return $isLabelDay ? $date->translatedFormat('d M') : '';
    }
}
