<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalPost;
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

        return Inertia::render('Modules/Portal/Post/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
        ]);
    }

    public function storeComment(Request $request, $slug)
    {
        $post = PortalPost::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'content' => 'required|string|max:1000',
        ]);

        // Secure against XSS
        $validated['content'] = strip_tags($validated['content']);
        $validated['status'] = 'approved'; // Assuming we auto-approve or handle in admin portal

        $post->comments()->create($validated);

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $posts = PortalPost::with(['category', 'user:id,name,foto_path'])
            ->whereIn('status', [PortalPost::STATUS_PUBLISHED, PortalPost::STATUS_SCHEDULED])
            ->where('published_at', '<=', now())
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Modules/Portal/Post/Index', [
            'posts' => $posts,
            'filters' => $request->only(['search']),
        ]);
    }
}
