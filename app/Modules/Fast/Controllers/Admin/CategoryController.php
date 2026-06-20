<?php
// app/Http/Controllers/Admin/CategoryController.php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/categories/Index', [
            'categories' => SuratCategory::orderBy('urutan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'icon'      => 'nullable|string|max:50',
            'warna'     => 'nullable|string|max:20',
            'urutan'    => 'nullable|integer',
        ]);

        $data['slug'] = Str::slug($data['nama']);

        SuratCategory::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, SuratCategory $category)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'icon'      => 'nullable|string|max:50',
            'warna'     => 'nullable|string|max:20',
            'urutan'    => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(SuratCategory $category)
    {
        if ($category->jenisSurats()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki jenis surat.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
