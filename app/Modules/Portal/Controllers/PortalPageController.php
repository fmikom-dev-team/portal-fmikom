<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PortalPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PortalPageController extends Controller
{
    public function index()
    {
        $pages = PortalPage::latest()->get();
        return Inertia::render('Modules/Portal/Admin/Pages', [
            'pages' => $pages,
            'categories' => ['profil', 'akademik', 'media', 'layanan', 'system'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:portal_pages,slug',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'meta_description' => 'nullable|string|max:255',
            'category' => 'nullable|string',
            'template' => 'nullable|string',
            'is_published' => 'boolean'
        ]);

        PortalPage::create($validated);
        return redirect()->back()->with('success', 'Page created successfully!');
    }

    public function show(PortalPage $page)
    {
        return Inertia::render('Modules/Portal/Admin/Pages/Editor', [
            'page' => $page,
        ]);
    }

    public function update(Request $request, PortalPage $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:portal_pages,slug,' . $page->id,
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'meta_description' => 'nullable|string|max:255',
            'category' => 'nullable|string',
            'template' => 'nullable|string',
            'is_published' => 'boolean'
        ]);

        $page->update($validated);
        return redirect()->back()->with('success', 'Page updated successfully!');
    }

    public function destroy(PortalPage $page)
    {
        $page->delete();
        return redirect()->back()->with('success', 'Page deleted successfully!');
    }
}
