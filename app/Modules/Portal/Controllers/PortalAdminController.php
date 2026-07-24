<?php

namespace App\Modules\Portal\Controllers;

use App\Concerns\HandlesImageCompression;
use App\Http\Controllers\Controller;
use App\Models\Portal\PortalComment;
use App\Models\Portal\PortalDocument;
use App\Models\Portal\PortalEvent;
use App\Models\Portal\PortalMedia;
use App\Models\Portal\PortalPage;
use App\Models\Portal\PortalPost;
use App\Models\Portal\PortalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PortalAdminController extends Controller
{
    use HandlesImageCompression;

    public function index()
    {
        $totalPosts = PortalPost::count();
        $totalEvents = PortalEvent::count();
        $totalMedia = PortalMedia::count();
        $pendingComments = PortalComment::where('status', 'pending')->count();

        $latestPosts = PortalPost::with('category')
            ->latest()
            ->take(5)
            ->get(['id', 'title', 'is_published', 'created_at', 'category_id']);

        $recentComments = PortalComment::with('post')
            ->latest()
            ->take(5)
            ->get(['id', 'author_name', 'content', 'status', 'created_at', 'post_id']);

        return Inertia::render('Modules/Portal/Admin/Dashboard', [
            'stats' => [
                'totalPosts' => $totalPosts,
                'totalEvents' => $totalEvents,
                'totalMedia' => $totalMedia,
                'pendingComments' => $pendingComments,
            ],
            'latestPosts' => $latestPosts,
            'recentComments' => $recentComments,
        ]);
    }

    public function appearance()
    {
        $settings = PortalSetting::pluck('value', 'key')->toArray();

        return Inertia::render('Modules/Portal/Admin/Appearance', [
            'settings' => $settings,
        ]);
    }

    public function updateAppearance(Request $request)
    {
        $validated = $request->validate([
            'hero_title' => 'nullable|string',
            'hero_subtitle' => 'nullable|string',
            'hero_description' => 'nullable|string',
            'show_navbar' => 'nullable|string',
            'show_hero' => 'nullable|string',
            'show_features' => 'nullable|string',
            'show_partners' => 'nullable|string',
            'show_benefits' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'benefits_title' => 'nullable|string',
            'benefits_subtitle' => 'nullable|string',
            'benefit_1_title' => 'nullable|string',
            'benefit_1_desc' => 'nullable|string',
            'benefit_2_title' => 'nullable|string',
            'benefit_2_desc' => 'nullable|string',
            'benefit_3_title' => 'nullable|string',
            'benefit_3_desc' => 'nullable|string',
            'hero_gallery_files' => 'nullable|array',
            'hero_gallery_files.*' => 'file|image|mimes:png,jpeg,jpg,webp|max:5120',
            'partner_files' => 'nullable|array',
            'partner_files.*' => 'file|image|mimes:png,jpeg,jpg,webp,svg|max:5120',
        ]);

        foreach ($validated as $key => $value) {
            if ($key !== 'hero_gallery_files' && $key !== 'partner_files') {
                PortalSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        // Handle Hero Gallery uploads — key is hero_gallery_files[]
        if ($request->hasFile('hero_gallery_files')) {
            $gallery = json_decode(PortalSetting::where('key', 'hero_gallery')->value('value') ?: '[]', true);
            foreach ($request->file('hero_gallery_files') as $file) {
                $path = $this->compressAndSaveImage($file, 'portal/gallery', 1200, 800);
                if ($path) {
                    $gallery[] = '/storage/'.$path;
                }
            }
            PortalSetting::updateOrCreate(['key' => 'hero_gallery'], ['value' => json_encode($gallery)]);
        }

        // Handle Partner uploads — key is partner_files[]
        if ($request->hasFile('partner_files')) {
            $partners = json_decode(PortalSetting::where('key', 'partners')->value('value') ?: '[]', true);
            foreach ($request->file('partner_files') as $file) {
                $path = $this->compressAndSaveImage($file, 'portal/partners', 400, 150);
                if ($path) {
                    $partners[] = '/storage/'.$path;
                }
            }
            PortalSetting::updateOrCreate(['key' => 'partners'], ['value' => json_encode($partners)]);
        }

        // Handle removals
        $gallery = json_decode(PortalSetting::where('key', 'hero_gallery')->value('value') ?: '[]', true);
        if ($request->has('remove_hero_gallery')) {
            foreach ($request->remove_hero_gallery as $url) {
                $gallery = array_values(array_filter($gallery, fn ($item) => $item !== $url));
                $filePath = str_replace('/storage/', '', $url);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
            PortalSetting::updateOrCreate(['key' => 'hero_gallery'], ['value' => json_encode($gallery)]);
        }

        $partners = json_decode(PortalSetting::where('key', 'partners')->value('value') ?: '[]', true);
        if ($request->has('remove_partners')) {
            foreach ($request->remove_partners as $url) {
                $partners = array_values(array_filter($partners, function ($item) use ($url) {
                    $itemUrl = is_array($item) ? ($item['logo'] ?? '') : $item;

                    return $itemUrl !== $url;
                }));
                $filePath = str_replace('/storage/', '', $url);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
            PortalSetting::updateOrCreate(['key' => 'partners'], ['value' => json_encode($partners)]);
        }

        return redirect()->back()->with('success', 'Layout settings updated successfully!');
    }

    public function settings()
    {
        $settings = PortalSetting::pluck('value', 'key')->toArray();

        return Inertia::render('Modules/Portal/Admin/Settings', [
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'posts_per_page' => 'nullable|integer|min:1|max:100',
            'allow_comments' => 'nullable|string',
            'moderate_comments' => 'nullable|string',
            'author_name' => 'nullable|string|max:100',
            'author_image_file' => 'nullable|image|max:2048',
        ]);

        foreach ($validated as $key => $value) {
            if ($key !== 'author_image_file') {
                PortalSetting::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
            }
        }

        if ($request->hasFile('author_image_file')) {
            $file = $request->file('author_image_file');

            // Delete old file if exists to prevent storage accumulation
            $oldPath = PortalSetting::where('key', 'author_image')->value('value');
            if ($oldPath) {
                $filePath = str_replace('/storage/', '', $oldPath);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            $path = $this->compressAndSaveImage($file, 'portal/author', 300, 300);
            if ($path) {
                PortalSetting::updateOrCreate(['key' => 'author_image'], ['value' => '/storage/'.$path]);
            }
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan!');
    }

    public function instantSearch(Request $request)
    {
        $q = $request->query('q', '');
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        try {
            // Meilisearch fast fuzzy multi-model search
            $posts = PortalPost::search($q)->take(3)->get()->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'type' => 'Post / Berita',
                'url' => "/portal-admin/posts/{$p->id}/edit",
            ]);

            $pages = PortalPage::search($q)->take(3)->get()->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'type' => 'Halaman Statis',
                'url' => '/portal-admin/pages',
            ]);

            $events = PortalEvent::search($q)->take(3)->get()->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'type' => 'Event / Kegiatan',
                'url' => '/portal-admin/events',
            ]);

            $docs = PortalDocument::search($q)->take(3)->get()->map(fn ($d) => [
                'id' => $d->id,
                'title' => $d->title,
                'type' => 'Dokumen / Arsip',
                'url' => '/portal-admin/documents',
            ]);

            $results = collect()
                ->concat($posts)
                ->concat($pages)
                ->concat($events)
                ->concat($docs)
                ->values();

            return response()->json(['results' => $results]);

        } catch (\Throwable $e) {
            Log::warning('Meilisearch instant search failed, falling back to SQL', ['error' => $e->getMessage()]);

            $posts = PortalPost::where('title', 'like', "%{$q}%")->take(3)->get()->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'type' => 'Post / Berita',
                'url' => "/portal-admin/posts/{$p->id}/edit",
            ]);

            $pages = PortalPage::where('title', 'like', "%{$q}%")->take(3)->get()->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'type' => 'Halaman Statis',
                'url' => '/portal-admin/pages',
            ]);

            $results = collect()->concat($posts)->concat($pages)->values();

            return response()->json(['results' => $results]);
        }
    }
}
