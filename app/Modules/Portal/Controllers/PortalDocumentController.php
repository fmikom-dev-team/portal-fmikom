<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalDocument;
use App\Services\VirusScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PortalDocumentController extends Controller
{
    protected $scanner;

    public function __construct(VirusScannerService $scanner)
    {
        $this->scanner = $scanner;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');

        $documents = PortalDocument::query()
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
            ->paginate(15)
            ->withQueryString();

        $categories = PortalDocument::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        return Inertia::render('Modules/Portal/Admin/Documents/Index', [
            'documents' => $documents,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_pinned' => 'boolean',
            'file' => 'required|file|mimes:pdf,docx,xlsx,pptx|max:10240', // Max 10MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Scan for virus signature
            $scanResult = $this->scanner->scan($file);
            if (! $scanResult['safe']) {
                throw ValidationException::withMessages([
                    'file' => $scanResult['reason'],
                ]);
            }

            $originalName = $file->getClientOriginalName();
            $uuid = Str::uuid()->toString();
            $extension = $file->getClientOriginalExtension();
            $fileNameSecure = $uuid.($extension ? '.'.$extension : '');

            // Store to private storage (local disk)
            $path = $file->storeAs('portal/documents', $fileNameSecure, 'local');

            $validated['file_path'] = $path;
            $validated['file_name'] = $originalName;
            $validated['file_size'] = $file->getSize();
        }

        unset($validated['file']);

        PortalDocument::create($validated);

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah!');
    }

    public function update(Request $request, PortalDocument $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_pinned' => 'boolean',
            'file' => 'nullable|file|mimes:pdf,docx,xlsx,pptx|max:10240', // Max 10MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Scan for virus signature
            $scanResult = $this->scanner->scan($file);
            if (! $scanResult['safe']) {
                throw ValidationException::withMessages([
                    'file' => $scanResult['reason'],
                ]);
            }

            // Delete old file
            $oldFilePath = $document->file_path;
            if (str_starts_with($oldFilePath, '/storage/')) {
                $publicPath = str_replace('/storage/', '', $oldFilePath);
                if (Storage::disk('public')->exists($publicPath)) {
                    Storage::disk('public')->delete($publicPath);
                }
            } else {
                if (Storage::disk('local')->exists($oldFilePath)) {
                    Storage::disk('local')->delete($oldFilePath);
                }
            }

            $originalName = $file->getClientOriginalName();
            $uuid = Str::uuid()->toString();
            $extension = $file->getClientOriginalExtension();
            $fileNameSecure = $uuid.($extension ? '.'.$extension : '');

            // Store to private storage
            $path = $file->storeAs('portal/documents', $fileNameSecure, 'local');

            $validated['file_path'] = $path;
            $validated['file_name'] = $originalName;
            $validated['file_size'] = $file->getSize();
        }

        unset($validated['file']);

        $document->update($validated);

        return redirect()->back()->with('success', 'Dokumen berhasil diperbarui!');
    }

    public function destroy(PortalDocument $document)
    {
        // Delete file
        $filePath = $document->file_path;
        if (str_starts_with($filePath, '/storage/')) {
            $publicPath = str_replace('/storage/', '', $filePath);
            if (Storage::disk('public')->exists($publicPath)) {
                Storage::disk('public')->delete($publicPath);
            }
        } else {
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }
        }

        $document->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus!');
    }

    public function togglePin(PortalDocument $document)
    {
        $document->update([
            'is_pinned' => ! $document->is_pinned,
        ]);

        return redirect()->back()->with('success', $document->is_pinned ? 'Dokumen dipasang di atas!' : 'Dokumen dilepas dari atas!');
    }
}
