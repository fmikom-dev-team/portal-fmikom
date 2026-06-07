<?php

namespace App\Modules\Pagi\Controllers;

use App\Concerns\HandlesImageCompression;
use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiTag;
use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Notifications\PagiNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PagiEditorController extends Controller
{
    use HandlesImageCompression;

    public function editor(Request $request)
    {
        $id = $request->query('id');
        $portfolio = null;
        if ($id) {
            $portfolio = PagiWork::with('tags')->findOrFail($id);
            if ($portfolio->user_id !== auth()->id()) {
                abort(403, 'Unauthorized action.');
            }

            // Populate preview and previews for the frontend
            $content = $portfolio->content;
            if (is_array($content)) {
                foreach ($content as &$block) {
                    if (isset($block['file_path'])) {
                        $block['preview'] = asset('storage/'.$block['file_path']);
                    }
                    if (isset($block['file_paths']) && is_array($block['file_paths'])) {
                        $block['previews'] = array_map(function ($path) {
                            return asset('storage/'.$path);
                        }, $block['file_paths']);
                    }
                }
                $portfolio->content = $content;
            }
        }

        $images = [
            [
                'id' => 1,
                'url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=1920&auto=format&fit=crop',
                'width' => 1920,
                'height' => 1080,
            ],
            [
                'id' => 2,
                'url' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?q=80&w=1080&auto=format&fit=crop',
                'width' => 1080,
                'height' => 1350,
            ],
            [
                'id' => 3,
                'url' => 'https://images.unsplash.com/photo-1605721911519-3dfeb3be25e7?q=80&w=1080&auto=format&fit=crop',
                'width' => 1080,
                'height' => 1350,
            ],
            [
                'id' => 4,
                'url' => 'https://images.unsplash.com/photo-1526244434195-e2bd668b549b?q=80&w=1920&auto=format&fit=crop',
                'width' => 1920,
                'height' => 1080,
            ],
            [
                'id' => 5,
                'url' => 'https://images.unsplash.com/photo-1554188718-d80a69a4a29c?q=80&w=1920&auto=format&fit=crop',
                'width' => 1920,
                'height' => 1080,
            ],
        ];

        return Inertia::render('Modules/Pagi/User/Editor', [
            'editor' => $portfolio,
            'mockImages' => $images,
        ]);
    }

    public function store(Request $request)
    {
        // Log incoming data for debugging
        Log::info('Incoming store request payload:', $request->except(['content', 'cover_image']));

        // Normalize string booleans from FormData
        if ($request->has('is_published')) {
            $request->merge([
                'is_published' => filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        try {
            $videoDurationAndSizeValidator = function ($attribute, $value, $fail) {
                if ($value && $value->isValid()) {
                    $mime = $value->getMimeType();
                    if (str_starts_with($mime, 'video/')) {
                        // Max 20MB limit
                        if ($value->getSize() > 20 * 1024 * 1024) {
                            $fail('Ukuran video maksimal adalah 20MB.');

                            return;
                        }
                        // Duration limit 60 seconds
                        $path = $value->getRealPath();
                        $escapedPath = escapeshellarg($path);
                        $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {$escapedPath} 2>&1";
                        $output = shell_exec($command);
                        if ($output !== null && is_numeric(trim($output))) {
                            $duration = (float) trim($output);
                            if ($duration > 60.5) {
                                $fail('Durasi video maksimal adalah 1 menit (60 detik).');
                            }
                        }
                    }
                }
            };

            $isPublished = filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN);

            $rules = [
                'title' => $isPublished ? 'required|string|max:255' : 'nullable|string|max:255',
                'content' => 'nullable|array',
                'content.*.type' => 'nullable|string',
                'content.*.file' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,mp4,mov,avi,webm,mkv,3gp,mp3,wav,ogg,pdf,zip,rar,tar,7z', 'max:102400', $videoDurationAndSizeValidator],
                'content.*.files.*' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,mp4,mov,avi,webm,mkv,3gp', 'max:102400', $videoDurationAndSizeValidator],
                'is_published' => 'boolean',
                'cover_image' => [$isPublished ? 'required' : 'nullable', 'file', 'extensions:jpeg,jpg,png,gif,mp4,mov,avi,webm,mkv,3gp', 'max:102400', $videoDurationAndSizeValidator],
                'category' => $isPublished ? 'required|string|max:100' : 'nullable|string|max:100',
                'tags' => $isPublished ? 'required|string' : 'nullable|string',
                'tools_used' => $isPublished ? 'required|string|max:255' : 'nullable|string|max:255',
                'description' => $isPublished ? 'required|string|max:2000' : 'nullable|string|max:2000',
                'visibility' => $isPublished ? 'required|string|in:Everyone,Private' : 'nullable|string|in:Everyone,Private',
                'collaborators' => 'nullable|string',
            ];

            $request->validate($rules);
        } catch (ValidationException $e) {
            Log::error('Store validation failed errors:', $e->errors());
            throw $e;
        }

        // Sanitasi input plain text
        $sanitizedTitle = strip_tags($request->title);
        $sanitizedDescription = strip_tags($request->description);
        $sanitizedCategory = strip_tags($request->category);
        $sanitizedTools = strip_tags($request->tools_used);
        $sanitizedTags = strip_tags($request->tags);
        $sanitizedVisibility = strip_tags($request->visibility ?: 'Everyone');

        $contentData = [];
        if ($request->has('content') && is_array($request->content)) {
            foreach ($request->content as $key => $block) {
                if (isset($block['type'])) {
                    $newBlock = $block;

                    // Sanitasi teks rich HTML untuk XSS
                    if (isset($newBlock['value']) && is_string($newBlock['value'])) {
                        $newBlock['value'] = $this->sanitizeHtmlContent($newBlock['value']);
                    }
                    if (isset($newBlock['initialValue']) && is_string($newBlock['initialValue'])) {
                        $newBlock['initialValue'] = $this->sanitizeHtmlContent($newBlock['initialValue']);
                    }
                    if (isset($newBlock['name']) && is_string($newBlock['name'])) {
                        $newBlock['name'] = strip_tags($newBlock['name']);
                    }
                    if (isset($newBlock['link']) && is_string($newBlock['link'])) {
                        $newBlock['link'] = strip_tags($newBlock['link']);
                    }

                    // Handle single file (image, video, audio, asset)
                    if ($request->hasFile("content.{$key}.file")) {
                        $file = $request->file("content.{$key}.file");
                        $mime = $file->getClientMimeType();
                        if (str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/')) {
                            $path = $this->compressAndSaveBannerOrVideo($file, 'pagi/works');
                        } else {
                            $path = $file->store('pagi/works', 'public');
                        }
                        $newBlock['file_path'] = $path;
                        unset($newBlock['file']); // Remove UploadedFile instance
                        unset($newBlock['preview']); // Remove blob URL
                    }

                    // Handle multiple files (photo_grid)
                    if ($request->hasFile("content.{$key}.files")) {
                        $paths = [];
                        foreach ($request->file("content.{$key}.files") as $file) {
                            $mime = $file->getClientMimeType();
                            if (str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/')) {
                                $paths[] = $this->compressAndSaveBannerOrVideo($file, 'pagi/works');
                            } else {
                                $paths[] = $file->store('pagi/works', 'public');
                            }
                        }
                        $newBlock['file_paths'] = $paths;
                        unset($newBlock['files']);
                        unset($newBlock['previews']);
                    }

                    $contentData[] = $newBlock;
                }
            }
        }

        // Penanganan kolaborator
        $collaborators = $request->input('collaborators');
        if (is_string($collaborators)) {
            $collaborators = json_decode($collaborators, true) ?: [];
        }
        if (! is_array($collaborators)) {
            $collaborators = [];
        }
        $newCollaborators = [];
        foreach ($collaborators as $c) {
            $cName = is_array($c) ? $c['name'] : $c;
            $newCollaborators[] = [
                'name' => strip_tags($cName),
                'status' => 'pending',
            ];
        }

        $hasFeaturedDetails = false;
        foreach ($contentData as &$block) {
            if (isset($block['type']) && $block['type'] === 'featured_details') {
                $block['collaborators'] = $newCollaborators;
                $hasFeaturedDetails = true;
                break;
            }
        }
        if (! $hasFeaturedDetails && ! empty($newCollaborators)) {
            $contentData[] = [
                'type' => 'featured_details',
                'collaborators' => $newCollaborators,
                'skills' => [],
                'tools' => [],
            ];
        }

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->compressAndSaveBannerOrVideo($request->file('cover_image'), 'pagi/covers');
        }

        $portfolio = PagiWork::create([
            'user_id' => auth()->id(),
            'title' => $sanitizedTitle,
            'content' => $contentData,
            'cover_image' => $coverPath,
            'category' => $sanitizedCategory,
            'tools_used' => $sanitizedTools,
            'description' => $sanitizedDescription,
            'visibility' => $sanitizedVisibility,
            'is_published' => $request->is_published ?? false,
        ]);

        // Send notifications to new collaborators
        foreach ($newCollaborators as $collab) {
            $targetUser = User::where('name', $collab['name'])->first();
            if ($targetUser && $targetUser->id !== auth()->id()) {
                $targetUser->notify(new PagiNotification(
                    'collaboration',
                    auth()->user()->name,
                    'mengajak Anda berkolaborasi pada proyek: "'.$portfolio->title.'"',
                    auth()->user()->foto_path ? (str_starts_with(auth()->user()->foto_path, 'http') ? auth()->user()->foto_path : asset('storage/'.auth()->user()->foto_path)) : null,
                    '/pagi/notifications',
                    [
                        'portfolio_id' => $portfolio->id,
                        'portfolio_title' => $portfolio->title,
                        'inviter_name' => auth()->user()->name,
                    ]
                ));
            }
        }

        // Parse and sync tags
        if ($request->has('tags') && ! empty($sanitizedTags)) {
            $tagNames = array_map('trim', explode(',', $sanitizedTags));
            $tagIds = [];
            foreach ($tagNames as $name) {
                if (empty($name)) {
                    continue;
                }

                $slug = Str::slug($name);
                $tag = PagiTag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'color' => '#6366f1']
                );

                if (! $tag->wasRecentlyCreated) {
                    $tag->increment('usage_count');
                }

                $tagIds[] = $tag->id;
            }
            $portfolio->tags()->sync($tagIds);
        }

        return redirect()->route('module.pagi.dashboard')->with('success', 'Portfolio created successfully!');
    }

    public function quickStore(Request $request)
    {
        $videoDurationValidator = function ($attribute, $value, $fail) {
            if ($value && $value->isValid()) {
                $mime = $value->getMimeType();
                if (str_starts_with($mime, 'video/')) {
                    $path = $value->getRealPath();
                    $escapedPath = escapeshellarg($path);
                    $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {$escapedPath} 2>&1";
                    $output = shell_exec($command);
                    if ($output !== null && is_numeric(trim($output))) {
                        $duration = (float) trim($output);
                        if ($duration > 60.5) {
                            $fail('Durasi video maksimal adalah 1 menit (60 detik).');
                        }
                    }
                }
            }
        };

        $request->validate([
            'title' => 'required|string|max:255',
            'cover_image' => ['required', 'file', 'extensions:jpeg,jpg,png,gif,mp4,mov,avi,webm,mkv,3gp', 'max:102400', $videoDurationValidator],
            'skills' => ['required', 'string', function ($attribute, $value, $fail) {
                $array = json_decode($value, true);
                if (! is_array($array) || count($array) < 1) {
                    $fail('Pilih minimal 1 keahlian (skills).');
                }
            }],
            'tools' => ['required', 'string', function ($attribute, $value, $fail) {
                $array = json_decode($value, true);
                if (! is_array($array) || count($array) < 1) {
                    $fail('Pilih minimal 1 tools.');
                }
            }],
            'completed_work_link' => 'nullable|url|max:255',
            'collaborators' => 'nullable|string',
            'client' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'industry' => 'nullable|string|max:255',
            'original_work_confirmed' => 'nullable|string',
            'cover_fit' => 'nullable|string|in:cover,contain',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->compressAndSaveBannerOrVideo($request->file('cover_image'), 'pagi/covers');
        }

        // Parse lists
        $skills = json_decode($request->input('skills', '[]'), true) ?: [];
        $tools = json_decode($request->input('tools', '[]'), true) ?: [];
        $collaborators = json_decode($request->input('collaborators', '[]'), true) ?: [];

        $newCollaborators = [];
        foreach ($collaborators as $c) {
            $cName = is_array($c) ? $c['name'] : $c;
            $newCollaborators[] = [
                'name' => $cName,
                'status' => 'pending',
            ];
        }

        // Save detailed structure into block format inside content column
        $contentData = [
            [
                'type' => 'featured_details',
                'skills' => $skills,
                'tools' => $tools,
                'completed_work_link' => $request->input('completed_work_link'),
                'collaborators' => $newCollaborators,
                'client' => $request->input('client'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'industry' => $request->input('industry'),
                'original_work_confirmed' => $request->input('original_work_confirmed') === 'true',
                'cover_fit' => $request->input('cover_fit', 'cover'),
            ],
        ];

        $portfolio = PagiWork::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $contentData,
            'cover_image' => $coverPath,
            'is_published' => true,
        ]);

        // Send notifications to new collaborators
        foreach ($newCollaborators as $collab) {
            $targetUser = User::where('name', $collab['name'])->first();
            if ($targetUser && $targetUser->id !== auth()->id()) {
                $targetUser->notify(new PagiNotification(
                    'collaboration',
                    auth()->user()->name,
                    'mengajak Anda berkolaborasi pada proyek: "'.$portfolio->title.'"',
                    auth()->user()->foto_path ? (str_starts_with(auth()->user()->foto_path, 'http') ? auth()->user()->foto_path : asset('storage/'.auth()->user()->foto_path)) : null,
                    '/pagi/notifications',
                    [
                        'portfolio_id' => $portfolio->id,
                        'portfolio_title' => $portfolio->title,
                        'inviter_name' => auth()->user()->name,
                    ]
                ));
            }
        }

        $mappedProject = [
            'id' => $portfolio->id,
            'title' => $portfolio->title ?? 'Untitled Project',
            'image' => $portfolio->cover_image ? (str_starts_with($portfolio->cover_image, 'http') ? $portfolio->cover_image : asset('storage/'.$portfolio->cover_image)) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
            'content' => $contentData,
            'created_at' => $portfolio->created_at->format('F jS Y'),
            'likes' => 0,
            'liked' => false,
            'comments' => [],
            'views' => 0,
        ];

        return response()->json([
            'success' => true,
            'project' => $mappedProject,
            'message' => 'Karya berhasil ditambahkan!',
        ]);
    }

    public function quickUpdate(Request $request, PagiWork $editor)
    {
        if ($editor->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // If it is a gallery item, bypass skills and tools validation
        $isGalleryItem = false;
        if (is_array($editor->content)) {
            foreach ($editor->content as $block) {
                if (isset($block['type']) && $block['type'] === 'gallery_item') {
                    $isGalleryItem = true;
                    break;
                }
            }
        }

        if ($isGalleryItem) {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:2000',
            ]);

            $content = $editor->content;
            if (is_array($content)) {
                foreach ($content as &$block) {
                    if (isset($block['type']) && $block['type'] === 'gallery_item') {
                        $block['description'] = $request->description;
                    }
                }
            }

            $editor->update([
                'title' => $request->title,
                'content' => $content,
            ]);

            return response()->json([
                'success' => true,
                'project' => [
                    'id' => $editor->id,
                    'title' => $editor->title,
                    'content' => $content,
                ],
                'message' => 'Gallery item updated successfully!',
            ]);
        }

        $videoDurationValidator = function ($attribute, $value, $fail) {
            if ($value && $value->isValid()) {
                $mime = $value->getMimeType();
                if (str_starts_with($mime, 'video/')) {
                    $path = $value->getRealPath();
                    $escapedPath = escapeshellarg($path);
                    $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {$escapedPath} 2>&1";
                    $output = shell_exec($command);
                    if ($output !== null && is_numeric(trim($output))) {
                        $duration = (float) trim($output);
                        if ($duration > 60.5) {
                            $fail('Durasi video maksimal adalah 1 menit (60 detik).');
                        }
                    }
                }
            }
        };

        $request->validate([
            'title' => 'required|string|max:255',
            'cover_image' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,mp4,mov,avi,webm,mkv,3gp', 'max:102400', $videoDurationValidator],
            'skills' => ['required', 'string', function ($attribute, $value, $fail) {
                $array = json_decode($value, true);
                if (! is_array($array) || count($array) < 1) {
                    $fail('Pilih minimal 1 keahlian (skills).');
                }
            }],
            'tools' => ['required', 'string', function ($attribute, $value, $fail) {
                $array = json_decode($value, true);
                if (! is_array($array) || count($array) < 1) {
                    $fail('Pilih minimal 1 tools.');
                }
            }],
            'completed_work_link' => 'nullable|url|max:255',
            'collaborators' => 'nullable|string',
            'client' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'industry' => 'nullable|string|max:255',
            'original_work_confirmed' => 'nullable|string',
            'cover_fit' => 'nullable|string|in:cover,contain',
        ]);

        $coverPath = $editor->cover_image;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->compressAndSaveBannerOrVideo($request->file('cover_image'), 'pagi/covers');
        }

        // Parse lists
        $skills = json_decode($request->input('skills', '[]'), true) ?: [];
        $tools = json_decode($request->input('tools', '[]'), true) ?: [];
        $collaborators = json_decode($request->input('collaborators', '[]'), true) ?: [];

        $existingDetails = collect($editor->content)->firstWhere('type', 'featured_details') ?: [];
        $existingCollabs = $existingDetails['collaborators'] ?? [];
        $existingMap = [];
        foreach ($existingCollabs as $ec) {
            if (is_array($ec)) {
                $existingMap[$ec['name']] = $ec['status'] ?? 'pending';
            } else {
                $existingMap[$ec] = 'accepted';
            }
        }

        $newCollaborators = [];
        $notifiedNames = [];
        foreach ($collaborators as $c) {
            $cName = is_array($c) ? $c['name'] : $c;
            if (isset($existingMap[$cName])) {
                $newCollaborators[] = [
                    'name' => $cName,
                    'status' => $existingMap[$cName],
                ];
            } else {
                $newCollaborators[] = [
                    'name' => $cName,
                    'status' => 'pending',
                ];
                $notifiedNames[] = $cName;
            }
        }

        // Save detailed structure into block format inside content column
        $contentData = [
            [
                'type' => 'featured_details',
                'skills' => $skills,
                'tools' => $tools,
                'completed_work_link' => $request->input('completed_work_link'),
                'collaborators' => $newCollaborators,
                'client' => $request->input('client'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'industry' => $request->input('industry'),
                'original_work_confirmed' => $request->input('original_work_confirmed') === 'true',
                'cover_fit' => $request->input('cover_fit', 'cover'),
            ],
        ];

        $editor->update([
            'title' => $request->title,
            'content' => $contentData,
            'cover_image' => $coverPath,
        ]);

        // Notify newly added collaborators
        foreach ($notifiedNames as $cName) {
            $targetUser = User::where('name', $cName)->first();
            if ($targetUser && $targetUser->id !== auth()->id()) {
                $targetUser->notify(new PagiNotification(
                    'collaboration',
                    auth()->user()->name,
                    'mengajak Anda berkolaborasi pada proyek: "'.$editor->title.'"',
                    auth()->user()->foto_path ? (str_starts_with(auth()->user()->foto_path, 'http') ? auth()->user()->foto_path : asset('storage/'.auth()->user()->foto_path)) : null,
                    '/pagi/notifications',
                    [
                        'portfolio_id' => $editor->id,
                        'portfolio_title' => $editor->title,
                        'inviter_name' => auth()->user()->name,
                    ]
                ));
            }
        }

        $mappedProject = [
            'id' => $editor->id,
            'title' => $editor->title ?? 'Untitled Project',
            'image' => $editor->cover_image ? (str_starts_with($editor->cover_image, 'http') ? $editor->cover_image : asset('storage/'.$editor->cover_image)) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
            'content' => $contentData,
            'created_at' => $editor->created_at->format('F jS Y'),
            'likes' => $editor->likes ?? 0,
            'liked' => false,
            'comments' => [],
            'views' => $editor->views ?? 0,
        ];

        return response()->json([
            'success' => true,
            'project' => $mappedProject,
            'message' => 'Karya berhasil diperbarui!',
        ]);
    }

    public function update(Request $request, PagiWork $editor)
    {
        if ($editor->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Log incoming data for debugging
        Log::info('Incoming update request payload:', $request->except(['content', 'cover_image']));

        // Normalize string booleans from FormData
        if ($request->has('is_published')) {
            $request->merge([
                'is_published' => filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        try {
            $videoDurationAndSizeValidator = function ($attribute, $value, $fail) {
                if ($value && $value->isValid()) {
                    $mime = $value->getMimeType();
                    if (str_starts_with($mime, 'video/')) {
                        // Max 20MB limit
                        if ($value->getSize() > 20 * 1024 * 1024) {
                            $fail('Ukuran video maksimal adalah 20MB.');

                            return;
                        }
                        // Duration limit 60 seconds
                        $path = $value->getRealPath();
                        $escapedPath = escapeshellarg($path);
                        $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {$escapedPath} 2>&1";
                        $output = shell_exec($command);
                        if ($output !== null && is_numeric(trim($output))) {
                            $duration = (float) trim($output);
                            if ($duration > 60.5) {
                                $fail('Durasi video maksimal adalah 1 menit (60 detik).');
                            }
                        }
                    }
                }
            };

            $isPublished = filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN);

            $rules = [
                'title' => $isPublished ? 'required|string|max:255' : 'nullable|string|max:255',
                'content' => 'nullable|array',
                'content.*.type' => 'nullable|string',
                'content.*.file' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,mp4,mov,avi,webm,mkv,3gp,mp3,wav,ogg,pdf,zip,rar,tar,7z', 'max:102400', $videoDurationAndSizeValidator],
                'content.*.files.*' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,mp4,mov,avi,webm,mkv,3gp', 'max:102400', $videoDurationAndSizeValidator],
                'is_published' => 'boolean',
                'cover_image' => ['nullable', 'file', 'extensions:jpeg,jpg,png,gif,mp4,mov,avi,webm,mkv,3gp', 'max:102400', $videoDurationAndSizeValidator],
                'category' => $isPublished ? 'required|string|max:100' : 'nullable|string|max:100',
                'tags' => $isPublished ? 'required|string' : 'nullable|string',
                'tools_used' => $isPublished ? 'required|string|max:255' : 'nullable|string|max:255',
                'description' => $isPublished ? 'required|string|max:2000' : 'nullable|string|max:2000',
                'visibility' => $isPublished ? 'required|string|in:Everyone,Private' : 'nullable|string|in:Everyone,Private',
                'collaborators' => 'nullable|string',
            ];

            $request->validate($rules);
        } catch (ValidationException $e) {
            Log::error('Update validation failed errors:', $e->errors());
            throw $e;
        }

        // Sanitasi input plain text
        $sanitizedTitle = strip_tags($request->title);
        $sanitizedDescription = strip_tags($request->description);
        $sanitizedCategory = strip_tags($request->category);
        $sanitizedTools = strip_tags($request->tools_used);
        $sanitizedTags = strip_tags($request->tags);
        $sanitizedVisibility = strip_tags($request->visibility ?: 'Everyone');

        $contentData = [];
        if ($request->has('content') && is_array($request->content)) {
            foreach ($request->content as $key => $block) {
                if (isset($block['type'])) {
                    $newBlock = $block;

                    // Sanitasi teks rich HTML untuk XSS
                    if (isset($newBlock['value']) && is_string($newBlock['value'])) {
                        $newBlock['value'] = $this->sanitizeHtmlContent($newBlock['value']);
                    }
                    if (isset($newBlock['initialValue']) && is_string($newBlock['initialValue'])) {
                        $newBlock['initialValue'] = $this->sanitizeHtmlContent($newBlock['initialValue']);
                    }
                    if (isset($newBlock['name']) && is_string($newBlock['name'])) {
                        $newBlock['name'] = strip_tags($newBlock['name']);
                    }
                    if (isset($newBlock['link']) && is_string($newBlock['link'])) {
                        $newBlock['link'] = strip_tags($newBlock['link']);
                    }

                    // Handle single file (image, video, audio, asset)
                    if ($request->hasFile("content.{$key}.file")) {
                        $file = $request->file("content.{$key}.file");
                        $mime = $file->getClientMimeType();
                        if (str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/')) {
                            $path = $this->compressAndSaveBannerOrVideo($file, 'pagi/works');
                        } else {
                            $path = $file->store('pagi/works', 'public');
                        }
                        $newBlock['file_path'] = $path;
                        unset($newBlock['file']); // Remove UploadedFile instance
                        unset($newBlock['preview']); // Remove blob URL
                    }

                    // Handle multiple files (photo_grid)
                    if ($request->hasFile("content.{$key}.files")) {
                        $paths = [];
                        foreach ($request->file("content.{$key}.files") as $file) {
                            $mime = $file->getClientMimeType();
                            if (str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/')) {
                                $paths[] = $this->compressAndSaveBannerOrVideo($file, 'pagi/works');
                            } else {
                                $paths[] = $file->store('pagi/works', 'public');
                            }
                        }
                        // Merge with existing file_paths if any
                        $existingPaths = $block['file_paths'] ?? [];
                        $newBlock['file_paths'] = array_merge($existingPaths, $paths);
                        unset($newBlock['files']);
                        unset($newBlock['previews']);
                    }

                    $contentData[] = $newBlock;
                }
            }
        }

        // Penanganan kolaborator
        $collaborators = $request->input('collaborators');
        if (is_string($collaborators)) {
            $collaborators = json_decode($collaborators, true) ?: [];
        }
        if (! is_array($collaborators)) {
            $collaborators = [];
        }
        $existingDetails = collect($editor->content)->firstWhere('type', 'featured_details') ?: [];
        $existingCollabs = $existingDetails['collaborators'] ?? [];
        $existingMap = [];
        foreach ($existingCollabs as $ec) {
            if (is_array($ec)) {
                $existingMap[$ec['name']] = $ec['status'] ?? 'pending';
            } else {
                $existingMap[$ec] = 'accepted';
            }
        }

        $newCollaborators = [];
        $notifiedNames = [];
        foreach ($collaborators as $c) {
            $cName = is_array($c) ? $c['name'] : $c;
            $cName = strip_tags($cName);
            if (isset($existingMap[$cName])) {
                $newCollaborators[] = [
                    'name' => $cName,
                    'status' => $existingMap[$cName],
                ];
            } else {
                $newCollaborators[] = [
                    'name' => $cName,
                    'status' => 'pending',
                ];
                $notifiedNames[] = $cName;
            }
        }

        $hasFeaturedDetails = false;
        foreach ($contentData as &$block) {
            if (isset($block['type']) && $block['type'] === 'featured_details') {
                $block['collaborators'] = $newCollaborators;
                $hasFeaturedDetails = true;
                break;
            }
        }
        if (! $hasFeaturedDetails && ! empty($newCollaborators)) {
            $contentData[] = [
                'type' => 'featured_details',
                'collaborators' => $newCollaborators,
                'skills' => [],
                'tools' => [],
            ];
        }

        $coverPath = $editor->cover_image;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->compressAndSaveBannerOrVideo($request->file('cover_image'), 'pagi/covers');
        }

        $editor->update([
            'title' => $sanitizedTitle,
            'content' => $contentData,
            'cover_image' => $coverPath,
            'category' => $sanitizedCategory,
            'tools_used' => $sanitizedTools,
            'description' => $sanitizedDescription,
            'visibility' => $sanitizedVisibility,
            'is_published' => $request->is_published ?? false,
        ]);

        // Send notifications to new collaborators
        foreach ($notifiedNames as $cName) {
            $targetUser = User::where('name', $cName)->first();
            if ($targetUser && $targetUser->id !== auth()->id()) {
                $targetUser->notify(new PagiNotification(
                    'collaboration',
                    auth()->user()->name,
                    'mengajak Anda berkolaborasi pada proyek: "'.$editor->title.'"',
                    auth()->user()->foto_path ? (str_starts_with(auth()->user()->foto_path, 'http') ? auth()->user()->foto_path : asset('storage/'.auth()->user()->foto_path)) : null,
                    '/pagi/notifications',
                    [
                        'portfolio_id' => $editor->id,
                        'portfolio_title' => $editor->title,
                        'inviter_name' => auth()->user()->name,
                    ]
                ));
            }
        }

        // Parse and sync tags
        if ($request->has('tags')) {
            $tagNames = ! empty($sanitizedTags) ? array_map('trim', explode(',', $sanitizedTags)) : [];
            $tagIds = [];
            foreach ($tagNames as $name) {
                if (empty($name)) {
                    continue;
                }

                $slug = Str::slug($name);
                $tag = PagiTag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'color' => '#6366f1']
                );

                if (! $tag->wasRecentlyCreated) {
                    $tag->increment('usage_count');
                }

                $tagIds[] = $tag->id;
            }
            $editor->tags()->sync($tagIds);
        }

        return redirect()->route('module.pagi.profile')->with('success', 'Portfolio updated successfully!');
    }

    public function destroy(PagiWork $editor)
    {
        if ($editor->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $editor->delete();

        return response()->json(['success' => true]);
    }

    public function acceptCollaboration(Request $request, PagiWork $editor)
    {
        $user = auth()->user();
        $content = $editor->content ?? [];

        // content might be an empty string in edge cases
        if (! is_array($content)) {
            $content = [];
        }

        $updated = false;

        foreach ($content as $key => $block) {
            if (! is_array($block)) {
                continue;
            }
            if (isset($block['type']) && $block['type'] === 'featured_details') {
                $collaborators = $block['collaborators'] ?? [];
                foreach ($collaborators as $cKey => $collab) {
                    $cName = is_array($collab) ? ($collab['name'] ?? '') : $collab;
                    if ($cName === $user->name) {
                        $collaborators[$cKey] = [
                            'name' => $cName,
                            'status' => 'accepted',
                        ];
                        $updated = true;
                    }
                }
                $content[$key]['collaborators'] = $collaborators;
            }
        }

        if ($updated) {
            $editor->update(['content' => $content]);

            // Notify the owner (inviter) that the collaborator accepted
            $owner = $editor->user;
            if ($owner && $owner->id !== $user->id) {
                $owner->notify(new PagiNotification(
                    'collaboration',
                    $user->pagi_username ?: $user->name,
                    'menerima ajakan kolaborasi pada proyek: "'.$editor->title.'"',
                    $user->foto_path ? (str_starts_with($user->foto_path, 'http') ? $user->foto_path : asset('storage/'.$user->foto_path)) : null,
                    '/pagi/profile',
                    [
                        'portfolio_id' => $editor->id,
                        'sender_id' => $user->id,
                    ]
                ));
            }
        }

        return response()->json(['success' => true]);
    }

    public function declineCollaboration(Request $request, PagiWork $editor)
    {
        $user = auth()->user();
        $content = $editor->content ?? [];

        // content might be an empty string in edge cases
        if (! is_array($content)) {
            $content = [];
        }

        $updated = false;

        foreach ($content as $key => $block) {
            if (! is_array($block)) {
                continue;
            }
            if (isset($block['type']) && $block['type'] === 'featured_details') {
                $collaborators = $block['collaborators'] ?? [];
                $collaborators = array_values(array_filter($collaborators, function ($collab) use ($user) {
                    $cName = is_array($collab) ? ($collab['name'] ?? '') : $collab;

                    return $cName !== $user->name;
                }));
                $content[$key]['collaborators'] = $collaborators;
                $updated = true;
            }
        }

        if ($updated) {
            $editor->update(['content' => $content]);
        }

        return response()->json(['success' => true]);
    }

    public function storeGalleryItem(Request $request)
    {
        $videoDurationValidator = function ($attribute, $value, $fail) {
            if ($value && $value->isValid()) {
                $mime = $value->getMimeType();
                if (str_starts_with($mime, 'video/')) {
                    $path = $value->getRealPath();
                    $escapedPath = escapeshellarg($path);
                    $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 {$escapedPath} 2>&1";
                    $output = shell_exec($command);
                    if ($output !== null && is_numeric(trim($output))) {
                        $duration = (float) trim($output);
                        if ($duration > 60.5) {
                            $fail('Durasi video maksimal adalah 1 menit (60 detik).');
                        }
                    }
                }
            }
        };

        $request->validate([
            'title' => 'nullable|string|max:255',
            'cover_image' => ['required', 'file', 'extensions:jpeg,jpg,png,gif,webp,mp4,mov,avi,webm,mkv,3gp', 'max:102400', $videoDurationValidator],
            'description' => 'nullable|string|max:2000',
        ]);

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $mime = $file->getClientMimeType();
            if (str_starts_with($mime, 'video/')) {
                $coverPath = $this->compressAndSaveBannerOrVideo($file, 'pagi/gallery');
            } else {
                $coverPath = $this->compressAndSaveImage($file, 'pagi/gallery', 1920, 1920, 85);
            }
        }

        $portfolio = PagiWork::create([
            'user_id' => auth()->id(),
            'title' => $request->title ?: 'Untitled Gallery Item',
            'content' => [
                [
                    'type' => 'gallery_item',
                    'description' => $request->description,
                ],
            ],
            'cover_image' => $coverPath,
            'is_published' => true,
        ]);

        $mapped = [
            'id' => $portfolio->id,
            'user_id' => $portfolio->user_id,
            'title' => $portfolio->title,
            'image' => asset('storage/'.$portfolio->cover_image),
            'content' => $portfolio->content,
            'created_at' => $portfolio->created_at->format('F jS Y'),
            'likes' => 0,
            'liked' => false,
            'comments' => [],
            'views' => 0,
            'is_published' => true,
            'tools_used' => null,
            'description' => $request->description,
            'category' => null,
            'tags' => [],
            'resolved_collaborators' => [],
            'user' => [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'pagi_username' => auth()->user()->pagi_username,
                'avatar' => auth()->user()->foto_path ? (str_starts_with(auth()->user()->foto_path, 'http') ? auth()->user()->foto_path : asset('storage/'.auth()->user()->foto_path)) : null,
                'location' => auth()->user()->location ?? 'Banyumas, Indonesia',
            ],
        ];

        return response()->json([
            'success' => true,
            'project' => $mapped,
            'message' => 'Gallery item added successfully!',
        ]);
    }

    private function sanitizeHtmlContent($html)
    {
        if (empty($html)) {
            return $html;
        }
        // Remove script tags and content
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        // Remove event handlers
        $html = preg_replace('/on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $html);
        // Remove javascript hrefs
        $html = preg_replace('/href\s*=\s*["\']\s*javascript:[^"\']*["\']/i', 'href="#"', $html);
        // Remove iframe/embed
        $html = preg_replace('/<(iframe|object|embed|applet)\b[^>]*>(.*?)<\/\1>/is', '', $html);

        return $html;
    }
}
