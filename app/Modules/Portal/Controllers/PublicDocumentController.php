<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalDocument;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PublicDocumentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');

        // Pinned documents are displayed at the top or in a separate card/list
        $pinnedDocuments = PortalDocument::where('is_pinned', true)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->latest()
            ->get();

        // Standard documents
        $documents = PortalDocument::where('is_pinned', false)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // Get all unique categories for filters
        $categories = PortalDocument::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        return Inertia::render('Modules/Portal/Document/Index', [
            'pinnedDocuments' => $pinnedDocuments,
            'documents' => $documents,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function download(PortalDocument $document)
    {
        $filePath = $document->file_path;

        // Backward compatibility: check if it's an old public disk file (prefixed with /storage/)
        if (str_starts_with($filePath, '/storage/')) {
            $publicPath = str_replace('/storage/', '', $filePath);
            /** @var FilesystemAdapter $publicDisk */
            $publicDisk = Storage::disk('public');
            if ($publicDisk->exists($publicPath)) {
                $document->increment('download_count');

                return $publicDisk->download($publicPath, $document->file_name);
            }
        }

        /** @var FilesystemAdapter $localDisk */
        $localDisk = Storage::disk('local');

        // New private local disk files
        if (! $localDisk->exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        $document->increment('download_count');

        return $localDisk->download($filePath, $document->file_name);
    }
}
