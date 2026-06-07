<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PortalCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PortalCategoryController extends Controller
{
    public function index()
    {
        $categories = PortalCategory::withCount('posts')->latest()->get();
        return Inertia::render('Modules/Portal/Admin/Categories', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:portal_categories,slug',
            'description' => 'nullable|string'
        ]);

        PortalCategory::create($validated);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, PortalCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:portal_categories,slug,' . $category->id,
            'description' => 'nullable|string'
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    public function destroy(PortalCategory $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
}
