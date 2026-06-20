<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalCategory;
use App\Models\Portal\PortalMedia;
use App\Models\Portal\PortalPost;
use App\Models\Portal\PortalSetting;
use App\Services\VirusScannerService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

        $settings = PortalSetting::pluck('value', 'key')->toArray();
        $perPage = isset($settings['posts_per_page']) && is_numeric($settings['posts_per_page']) ? (int) $settings['posts_per_page'] : 15;

        $posts = PortalPost::with('category')
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

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
        Log::info('Post store attempt', ['user_id' => Auth::id(), 'title' => $request->input('title'), 'slug' => $request->input('slug')]);
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
                'og_image' => 'nullable',
                'thumbnail' => 'nullable',
            ]);

            foreach (['og_image', 'thumbnail'] as $field) {
                if ($request->hasFile($field)) {
                    $request->validate([
                        $field => 'image|max:5120',
                    ]);
                } elseif ($request->filled($field)) {
                    $request->validate([
                        $field => 'string|max:255',
                    ]);
                }
            }
        } catch (ValidationException $e) {
            Log::error('STORE Validation Failed:', $e->errors());
            throw $e;
        }

        // Content is stored as Editor.js JSON — no HTML purification needed.
        // Just verify it's valid JSON (not injected script).
        $content = $request->input('content', '');
        if (! empty($content)) {
            json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['content' => 'Format konten tidak valid.']);
            }
            $validated['content'] = $content; // store raw JSON string
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->processAndStoreImage($request->file('thumbnail'), 'portal/posts/thumbnails');
        } elseif ($request->filled('thumbnail')) {
            $validated['thumbnail'] = $request->input('thumbnail');
        } else {
            unset($validated['thumbnail']);
        }

        if ($request->hasFile('og_image')) {
            $validated['og_image'] = $this->processAndStoreImage($request->file('og_image'), 'portal/posts/seo');
        } elseif ($request->filled('og_image')) {
            $validated['og_image'] = $request->input('og_image');
        } else {
            unset($validated['og_image']);
        }

        if (isset($validated['tags']) && is_array($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        } else {
            $validated['tags'] = json_encode([]);
        }

        $validated['user_id'] = Auth::id();

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
        Log::info('Post update attempt', ['user_id' => Auth::id(), 'post_id' => $post->id, 'title' => $request->input('title')]);
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
                'og_image' => 'nullable',
                'thumbnail' => 'nullable',
            ]);

            foreach (['og_image', 'thumbnail'] as $field) {
                if ($request->hasFile($field)) {
                    $request->validate([
                        $field => 'image|max:5120',
                    ]);
                } elseif ($request->filled($field)) {
                    $request->validate([
                        $field => 'string|max:255',
                    ]);
                }
            }
        } catch (ValidationException $e) {
            Log::error('UPDATE Validation Failed:', $e->errors());
            throw $e;
        }

        // Content is Editor.js JSON — store as-is after JSON validation
        $content = $request->input('content', '');
        if (! empty($content)) {
            json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['content' => 'Format konten tidak valid.']);
            }
            $validated['content'] = $content;
        }

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($post->thumbnail && ! str_contains($post->thumbnail, 'portal/media/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->thumbnail));
            }
            $validated['thumbnail'] = $this->processAndStoreImage($request->file('thumbnail'), 'portal/posts/thumbnails');
        } elseif ($request->filled('thumbnail') || $request->input('thumbnail') === null) {
            $validated['thumbnail'] = $request->input('thumbnail');
        } else {
            unset($validated['thumbnail']);
        }

        if ($request->hasFile('og_image')) {
            // Delete old og_image if exists
            if ($post->og_image && ! str_contains($post->og_image, 'portal/media/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->og_image));
            }
            $validated['og_image'] = $this->processAndStoreImage($request->file('og_image'), 'portal/posts/seo');
        } elseif ($request->filled('og_image') || $request->input('og_image') === null) {
            $validated['og_image'] = $request->input('og_image');
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

            // BUG-019: Validate URL safety to prevent tracking pixels and private network access
            if (! $this->isUrlSafeForFetch($url)) {
                return response()->json(['success' => 0, 'message' => 'URL tidak diizinkan.'], 422);
            }

            try {
                // Fetch external image content
                $response = Http::timeout(8)
                    ->connectTimeout(3)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; PortalFMIKOM/1.0)'])
                    ->get($url);

                if (! $response->successful()) {
                    return response()->json(['success' => 0, 'message' => 'Gagal mengambil gambar dari URL.'], 422);
                }

                // Verify content-type is an image
                $contentType = $response->header('Content-Type');
                if (! str_starts_with($contentType, 'image/')) {
                    return response()->json(['success' => 0, 'message' => 'URL bukan merupakan gambar.'], 422);
                }

                $imageContent = $response->body();
                if (strlen($imageContent) > 10 * 1024 * 1024) {
                    return response()->json(['success' => 0, 'message' => 'Ukuran gambar melebihi batas 10MB.'], 422);
                }

                // Process and store the downloaded image content using processAndStoreImage
                $tempPath = tempnam(sys_get_temp_dir(), 'img_url_');
                file_put_contents($tempPath, $imageContent);

                try {
                    $localUrl = $this->processAndStoreImage($tempPath, 'portal/posts/content');
                } finally {
                    @unlink($tempPath);
                }

                return response()->json([
                    'success' => 1,
                    'file' => ['url' => $localUrl],
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => 0, 'message' => 'Gagal mengambil gambar dari URL: '.$e->getMessage()], 500);
            }
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

            $scanner = app(VirusScannerService::class);
            $scanResult = $scanner->scan($file);
            if (! $scanResult['safe']) {
                return response()->json([
                    'success' => 0,
                    'message' => $scanResult['reason'],
                ], 422);
            }

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
     * Fetch URL metadata for Editor.js link block tool.
     * SEC-005 / BUG-018: Strict URL validation blocks SSRF to internal network.
     */
    public function fetchUrl(Request $request)
    {
        $url = $request->input('url');
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['success' => 0]);
        }

        // Block SSRF: reject private, loopback, and link-local IP ranges
        if (! $this->isUrlSafeForFetch($url)) {
            return response()->json(['success' => 0, 'message' => 'URL tidak diizinkan.']);
        }

        try {
            $client = new Client([
                'timeout' => 5,
                'connect_timeout' => 3,
                'allow_redirects' => ['max' => 3],
                'verify' => true,
            ]);

            $response = $client->get($url, [
                'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; PortalFMIKOM/1.0)'],
            ]);

            $html = (string) $response->getBody();

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
     * Validate that a URL is safe to fetch (no internal/private network access).
     * Blocks RFC 1918 private ranges, loopback, link-local, and SSRF targets.
     */
    private function isUrlSafeForFetch(string $url): bool
    {
        $parsed = parse_url($url);

        // Only allow http/https
        if (! isset($parsed['scheme']) || ! in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
            return false;
        }

        $host = $parsed['host'] ?? '';
        if (empty($host)) {
            return false;
        }

        // Resolve to IP
        $ip = gethostbyname($host);
        if ($ip === $host && ! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false; // Could not resolve
        }

        // Block loopback
        if (in_array($ip, ['127.0.0.1', '::1']) || $host === 'localhost') {
            return false;
        }

        // Block private/reserved ranges using PHP built-in flags
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        return true;
    }

    /**
     * Process image: resize, compress, convert to webp and store.
     */
    private function processAndStoreImage($file, $path)
    {
        $uploadedFile = $file;
        if (is_string($file)) {
            $uploadedFile = new UploadedFile(
                $file,
                basename($file),
                mime_content_type($file),
                null,
                true
            );
        }

        if ($uploadedFile instanceof UploadedFile) {
            $scanner = app(VirusScannerService::class);
            $scanResult = $scanner->scan($uploadedFile);
            if (! $scanResult['safe']) {
                throw ValidationException::withMessages([
                    'thumbnail' => $scanResult['reason'],
                ]);
            }
        }

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

        $publicUrl = '/storage/'.$fullPath;

        // Automatically save to PortalMedia (Gallery)
        try {
            $originalName = is_object($file) && method_exists($file, 'getClientOriginalName')
                ? $file->getClientOriginalName()
                : basename($fullPath);

            $size = Storage::disk('public')->size($fullPath);

            PortalMedia::create([
                'filename' => $originalName,
                'path' => $publicUrl,
                'mime_type' => 'image/webp',
                'size' => $size,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to auto-save post image to PortalMedia: '.$e->getMessage());
        }

        return $publicUrl;
    }
}
