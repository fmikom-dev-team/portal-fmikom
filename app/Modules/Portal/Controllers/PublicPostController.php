<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalPost;
use App\Models\Portal\PortalSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicPostController extends Controller
{
    public function show($slug)
    {
        $post = PortalPost::with(['category', 'user:id,name,foto_path', 'comments' => function ($query) {
            $query->where('status', 'approved')->latest();
        }])
            ->where('slug', $slug)
            ->whereIn('status', [PortalPost::STATUS_PUBLISHED, PortalPost::STATUS_SCHEDULED])
            ->where('published_at', '<=', now())
            ->firstOrFail();

        // Get related posts (same category, excluding current post)
        $relatedPosts = PortalPost::with(['category', 'user:id,name,foto_path'])
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->whereIn('status', [PortalPost::STATUS_PUBLISHED, PortalPost::STATUS_SCHEDULED])
            ->where('published_at', '<=', now())
            ->latest()
            ->take(3)
            ->get();

        $previousPost = PortalPost::where('published_at', '<', $post->published_at)
            ->whereIn('status', [PortalPost::STATUS_PUBLISHED, PortalPost::STATUS_SCHEDULED])
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->first(['id', 'title', 'slug', 'meta_description']);

        $nextPost = PortalPost::where('published_at', '>', $post->published_at)
            ->whereIn('status', [PortalPost::STATUS_PUBLISHED, PortalPost::STATUS_SCHEDULED])
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'asc')
            ->first(['id', 'title', 'slug', 'meta_description']);

        $settings = PortalSetting::pluck('value', 'key')->toArray();

        return Inertia::render('Modules/Portal/Post/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
            'settings' => $settings,
        ]);
    }

    public function storeComment(Request $request, $slug)
    {
        if ($request->filled('phone')) {
            // Pretend success to deter spam bots
            return back()->with('success', 'Komentar berhasil ditambahkan.');
        }

        $settings = PortalSetting::pluck('value', 'key')->toArray();
        if (isset($settings['allow_comments']) && $settings['allow_comments'] === '0') {
            return back()->withErrors(['message' => 'Fitur komentar dinonaktifkan.']);
        }

        $post = PortalPost::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'content' => 'required|string|max:1000',
        ]);

        // Secure against XSS
        $validated['content'] = strip_tags($validated['content']);
        $validated['status'] = (isset($settings['moderate_comments']) && $settings['moderate_comments'] === '1') ? 'pending' : 'approved';

        $post->comments()->create($validated);

        $msg = $validated['status'] === 'pending'
            ? 'Komentar berhasil ditambahkan dan sedang menunggu moderasi admin.'
            : 'Komentar berhasil ditambahkan.';

        return back()->with('success', $msg);
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $settings = PortalSetting::pluck('value', 'key')->toArray();
        $perPage = isset($settings['posts_per_page']) && is_numeric($settings['posts_per_page']) ? (int) $settings['posts_per_page'] : 12;

        $posts = PortalPost::with(['category', 'user:id,name,foto_path'])
            ->whereIn('status', [PortalPost::STATUS_PUBLISHED, PortalPost::STATUS_SCHEDULED])
            ->where('published_at', '<=', now())
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Modules/Portal/Post/Index', [
            'posts' => $posts,
            'filters' => $request->only(['search']),
            'settings' => $settings,
        ]);
    }
}
