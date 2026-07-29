<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiWork;
use App\Models\Portal\PortalSetting;
use App\Modules\Pagi\Controllers\Concerns\HasAdminDashboardHelpers;
use App\Notifications\PagiNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin Showcase Controller
 * Mengelola karya mahasiswa unggulan (Featured Showcase) untuk Halaman Depan / Landing Page
 */
class AdminShowcaseController extends Controller
{
    use HasAdminDashboardHelpers;

    public function index(Request $request): Response
    {
        if (PagiWork::query()->count('*') === 0) {
            $this->seedPagiDemoData();
        }

        // Fetch all active published works for admin selection with completeness check
        $allWorks = PagiWork::query()->with(['user', 'tags'])
            ->where('is_published', '=', true, 'and')
            ->where('status', '=', 'active', 'and')
            ->latest('created_at')
            ->get()
            ->map(function ($w) {
                $hasCover = ! empty($w->cover_image);
                $hasDescription = ! empty($w->description) && mb_strlen($w->description) >= 20;
                $isComplete = $hasCover && $hasDescription;

                $missing = [];
                if (! $hasCover) {
                    $missing[] = 'Foto Sampul/Cover';
                }
                if (! $hasDescription) {
                    $missing[] = 'Deskripsi Karya';
                }

                $avatarUrl = $w->user && $w->user->foto_path ? $this->getStorageUrl($w->user->foto_path) : null;

                return [
                    'id' => $w->id,
                    'title' => $w->title,
                    'author' => $w->user->name ?? 'Mahasiswa',
                    'authorAvatar' => $avatarUrl,
                    'category' => $w->category ?? 'Design & UI/UX',
                    'tags' => $w->tags->pluck('name')->toArray(),
                    'thumbnail' => $this->getStorageUrl($w->cover_image),
                    'views' => $w->views_count,
                    'description' => $w->description ?? '',
                    'isComplete' => $isComplete,
                    'missingFields' => $missing,
                    'createdAt' => $w->created_at ? $w->created_at->format('d M Y') : 'Terverifikasi',
                ];
            });

        // Get saved showcase settings
        $keys = [
            'pagi_showcase_eyebrow',
            'pagi_showcase_title',
            'pagi_showcase_description',
            'pagi_showcase_work_ids',
        ];
        $settings = PortalSetting::whereIn('key', $keys)->pluck('value', 'key');

        $eyebrow = $settings->get('pagi_showcase_eyebrow', 'Inovasi Mahasiswa FMIKOM');
        $title = $settings->get('pagi_showcase_title', 'Portofolio Unggulan Berstandar Industri');
        $description = $settings->get('pagi_showcase_description', 'Jelajahi karya teknologi, desain antarmuka, dan aplikasi buatan mahasiswa FMIKOM yang telah dikurasi secara ketat.');

        $selectedIdsRaw = $settings->get('pagi_showcase_work_ids', '[]');
        $selectedWorkIds = json_decode($selectedIdsRaw, true) ?? [];

        return Inertia::render('Modules/Pagi/Admin/Showcase/Index', [
            'allWorks' => $allWorks,
            'showcaseConfig' => [
                'eyebrow' => $eyebrow,
                'title' => $title,
                'description' => $description,
                'selectedWorkIds' => $selectedWorkIds,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'eyebrow' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'selectedWorkIds' => ['nullable', 'array', 'max:6'],
            'selectedWorkIds.*' => ['integer', 'exists:pagi_works,id'],
        ]);

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_showcase_eyebrow'],
            ['value' => strip_tags($validated['eyebrow'])]
        );
        PortalSetting::updateOrCreate(
            ['key' => 'pagi_showcase_title'],
            ['value' => strip_tags($validated['title'])]
        );
        PortalSetting::updateOrCreate(
            ['key' => 'pagi_showcase_description'],
            ['value' => strip_tags($validated['description'] ?? '')]
        );
        PortalSetting::updateOrCreate(
            ['key' => 'pagi_showcase_work_ids'],
            ['value' => json_encode(array_values($validated['selectedWorkIds']))]
        );

        return redirect()->back()->with('success', 'Konfigurasi Karya Terbaik berhasil diperbarui!');
    }

    public function toggleShowcase(Request $request, PagiWork $work)
    {
        $setting = PortalSetting::where('key', 'pagi_showcase_work_ids')->first();
        $selectedIds = json_decode($setting->value ?? '[]', true) ?? [];

        if (in_array($work->id, $selectedIds)) {
            $selectedIds = array_values(array_filter($selectedIds, fn ($id) => $id != $work->id));
            $msg = 'Karya berhasil dihapus dari Karya Terbaik!';
        } else {
            if (count($selectedIds) >= 6) {
                return redirect()->back()->with('error', 'Maksimal 6 karya terbaik yang dapat ditayangkan di Landing Page.');
            }
            $selectedIds[] = $work->id;
            $msg = 'Karya berhasil ditambahkan ke Karya Terbaik!';
        }

        PortalSetting::updateOrCreate(
            ['key' => 'pagi_showcase_work_ids'],
            ['value' => json_encode($selectedIds)]
        );

        return redirect()->back()->with('success', $msg);
    }

    public function requestCompleteness(Request $request, PagiWork $work)
    {
        if ($work->user) {
            $missing = [];
            if (empty($work->cover_image)) {
                $missing[] = 'Foto Sampul/Cover';
            }
            if (empty($work->description) || mb_strlen($work->description) < 20) {
                $missing[] = 'Deskripsi Ringkas';
            }
            $missingText = ! empty($missing) ? implode(' & ', $missing) : 'kelengkapan data';

            // Send notification to user
            if ($work->user) {
                $work->user->notify(new PagiNotification(
                    type: 'system',
                    title: '🌟 Karya Anda Terpilih Kandidat Karya Terbaik!',
                    message: 'Selamat! Karya Anda "'.$work->title.'" terpilih untuk ditayangkan di Halaman Utama Portal FMIKOM. Harap lengkapi '.$missingText.' karya Anda agar dapat ditayangkan secara maksimal.',
                    avatar: null,
                    href: '/pagi/works/'.$work->id
                ));
            }
        }

        return redirect()->back()->with('success', 'Notifikasi permintaan kelengkapan karya berhasil dikirimkan ke mahasiswa!');
    }
}
