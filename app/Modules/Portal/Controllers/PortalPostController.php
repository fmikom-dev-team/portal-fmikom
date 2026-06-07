<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalCategory;
use App\Models\Portal\PortalPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PortalPostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $posts = PortalPost::with('category')
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return Inertia::render('Modules/Portal/Admin/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Modules/Portal/Admin/Posts/Create', [
            'categories' => PortalCategory::all(),
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Post store attempt', ['user_id' => auth()->id(), 'title' => $request->input('title'), 'slug' => $request->input('slug')]);
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|string|unique:portal_posts,slug',
                'content' => 'required|string',
                'category_id' => 'nullable|exists:portal_categories,id',
                'status' => 'required|in:draft,published,scheduled',
                'published_at' => 'nullable|date',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:160',
                'excerpt' => 'nullable|string',
                'tags' => 'nullable|array',
                'og_image' => 'nullable|image|max:5120',
                'thumbnail' => 'nullable|image|max:5120',
            ]);
        } catch (ValidationException $e) {
            Log::error('STORE Validation Failed:', $e->errors());
            throw $e;
        }

        // Content is stored as Editor.js JSON — no HTML purification needed.
        // Just verify it's valid JSON (not injected script).
        $content = $request->input('content', '');
        if (! empty($content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['content' => 'Format konten tidak valid.']);
            }
            $validated['content'] = $content; // store raw JSON string
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->processAndStoreImage($request->file('thumbnail'), 'portal/posts/thumbnails');
        } else {
            unset($validated['thumbnail']);
        }

        if ($request->hasFile('og_image')) {
            $validated['og_image'] = $this->processAndStoreImage($request->file('og_image'), 'portal/posts/seo');
        } else {
            unset($validated['og_image']);
        }

        if (isset($validated['tags']) && is_array($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        } else {
            $validated['tags'] = json_encode([]);
        }

        $validated['user_id'] = auth()->id();

        if ($validated['status'] === 'published') {
            $publishedAt = $validated['published_at'] ?? null;
            if ($publishedAt && strtotime($publishedAt) > time()) {
                $validated['status'] = PortalPost::STATUS_SCHEDULED;
            } elseif (! $publishedAt) {
                $validated['published_at'] = now();
            }
        }

        PortalPost::create($validated);

        return redirect()->route('portal-admin.posts.index')->with('success', 'Post created successfully!');
    }

    public function edit(PortalPost $post)
    {
        return Inertia::render('Modules/Portal/Admin/Posts/Edit', [
            'post' => $post,
            'categories' => PortalCategory::all(),
        ]);
    }

    public function update(Request $request, PortalPost $post)
    {
        Log::info('Post update attempt', ['user_id' => auth()->id(), 'post_id' => $post->id, 'title' => $request->input('title')]);
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'required|string|unique:portal_posts,slug,'.$post->id,
                'content' => 'required|string',
                'category_id' => 'nullable|exists:portal_categories,id',
                'status' => 'required|in:draft,published,scheduled',
                'published_at' => 'nullable|date',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:160',
                'excerpt' => 'nullable|string',
                'tags' => 'nullable|array',
                'og_image' => 'nullable|image|max:5120',
                'thumbnail' => 'nullable|image|max:5120',
            ]);
        } catch (ValidationException $e) {
            Log::error('UPDATE Validation Failed:', $e->errors());
            throw $e;
        }

        // Content is Editor.js JSON — store as-is after JSON validation
        $content = $request->input('content', '');
        if (! empty($content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['content' => 'Format konten tidak valid.']);
            }
            $validated['content'] = $content;
        }

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($post->thumbnail) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->thumbnail));
            }
            $validated['thumbnail'] = $this->processAndStoreImage($request->file('thumbnail'), 'portal/posts/thumbnails');
        } else {
            unset($validated['thumbnail']);
        }

        if ($request->hasFile('og_image')) {
            // Delete old og_image if exists
            if ($post->og_image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->og_image));
            }
            $validated['og_image'] = $this->processAndStoreImage($request->file('og_image'), 'portal/posts/seo');
        } else {
            unset($validated['og_image']);
        }

        if (isset($validated['tags']) && is_array($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        } else {
            $validated['tags'] = json_encode([]);
        }

        if ($validated['status'] === 'published') {
            $publishedAt = $validated['published_at'] ?? null;
            if ($publishedAt && strtotime($publishedAt) > time()) {
                $validated['status'] = PortalPost::STATUS_SCHEDULED;
            } elseif (! $post->published_at && ! $publishedAt) {
                $validated['published_at'] = now();
            }
        }

        $post->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Post saved successfully', 'post' => $post]);
        }

        return redirect()->route('portal-admin.posts.index')->with('success', 'Post updated successfully!');
    }

    public function destroy(PortalPost $post)
    {
        // Delete images
        if ($post->thumbnail) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $post->thumbnail));
        }
        if ($post->og_image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $post->og_image));
        }

        $post->delete();

        return redirect()->route('portal-admin.posts.index')->with('success', 'Post deleted successfully!');
    }

    /**
     * Upload inline image — handles both:
     * - byFile: multipart/form-data with field "image"
     * - byUrl:  JSON body with field "url" (when ?by_url=1)
     *
     * Returns Editor.js format: {success: 1, file: {url: '...'}}
     */
    public function uploadImage(Request $request)
    {
        // ── byUrl mode (image from external URL) ──
        if ($request->query('by_url') || $request->has('url')) {
            $url = $request->input('url');

            if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
                return response()->json(['success' => 0, 'message' => 'URL tidak valid.'], 422);
            }

            // Return the URL directly — Editor.js will render it from original URL
            return response()->json([
                'success' => 1,
                'file' => ['url' => $url],
            ]);
        }

        // ── byFile mode (file upload) ──
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ]);

        if ($request->hasFile('image')) {
            $url = $this->processAndStoreImage(
                $request->file('image'),
                'portal/posts/content'
            );

            return response()->json([
                'success' => 1,
                'file' => ['url' => $url],
                'url' => $url,
            ]);
        }

        return response()->json(['success' => 0, 'message' => 'Upload gagal.'], 400);
    }

    /**
     * Upload file attachment (used by Editor.js @editorjs/attaches tool).
     * Returns Editor.js format: {success: 1, file: {url, name, size, extension}}
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,txt,csv|max:51200', // 50MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::random(30).'.'.$file->getClientOriginalExtension();
            $path = 'portal/posts/files/'.$filename;

            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

            return response()->json([
                'success' => 1,
                'file' => [
                    'url' => '/storage/'.$path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'extension' => $file->getClientOriginalExtension(),
                ],
            ]);
        }

        return response()->json(['success' => 0, 'message' => 'Upload failed'], 400);
    }

    /**
     * Fetch URL metadata for Editor.js link block tool
     */
    public function fetchUrl(Request $request)
    {
        $url = $request->input('url');
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['success' => 0]);
        }

        try {
            $html = @file_get_contents($url);
            if (! $html) {
                return response()->json(['success' => 0]);
            }

            $doc = new \DOMDocument;
            @$doc->loadHTML($html);

            $title = '';
            $description = '';
            $image = '';

            $nodes = $doc->getElementsByTagName('title');
            if ($nodes->length > 0) {
                $title = $nodes->item(0)->nodeValue;
            }

            $metas = $doc->getElementsByTagName('meta');
            for ($i = 0; $i < $metas->length; $i++) {
                $meta = $metas->item($i);
                if ($meta->getAttribute('property') == 'og:title') {
                    $title = $meta->getAttribute('content');
                }
                if ($meta->getAttribute('name') == 'description' || $meta->getAttribute('property') == 'og:description') {
                    $description = $meta->getAttribute('content');
                }
                if ($meta->getAttribute('property') == 'og:image') {
                    $image = $meta->getAttribute('content');
                }
            }

            return response()->json([
                'success' => 1,
                'meta' => [
                    'title' => $title ?: $url,
                    'description' => $description,
                    'image' => [
                        'url' => $image,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => 0]);
        }
    }

    /**
     * Process image: resize, compress, convert to webp and store.
     */
    private function processAndStoreImage($file, $path)
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->read($file);

        // Resize if wider than 1200px
        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }

        // Generate random filename with webp extension
        $filename = Str::random(40).'.webp';
        $fullPath = $path.'/'.$filename;

        // Encode as WebP with 80% quality
        $encoded = $image->toWebp(80);

        // Store in public disk
        Storage::disk('public')->put($fullPath, (string) $encoded);

        return '/storage/'.$fullPath;
    }
}
