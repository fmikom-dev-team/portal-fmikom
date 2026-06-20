<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalCategory;
use App\Models\Portal\PortalComment;
use App\Models\Portal\PortalMedia;
use App\Models\Portal\PortalPost;
use App\Models\Portal\PortalSetting;
use App\Concerns\HandlesImageCompression;
use App\Services\VirusScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PortalAdminController extends Controller
{
    use HandlesImageCompression;

    public function index()
    {
        $totalPosts = PortalPost::count();
        $totalCategories = PortalCategory::count();
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
                'totalCategories' => $totalCategories,
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
        ]);

        foreach ($validated as $key => $value) {
            PortalSetting::updateOrCreate(['key' => $key], ['value' => $value]);
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
                $partners = array_values(array_filter($partners, fn ($item) => $item !== $url));
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
}
