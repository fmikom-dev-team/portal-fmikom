<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PortalMenu;
use App\Models\PortalPage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalMenuController extends Controller
{
    public function index()
    {
        $menus = PortalMenu::with(['page', 'children.page'])
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
            
        $pages = PortalPage::all(['id', 'title', 'slug']);

        return Inertia::render('Modules/Portal/Admin/Menus', [
            'menus' => $menus,
            'pages' => $pages
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'portal_page_id' => 'nullable|exists:portal_pages,id',
            'parent_id' => 'nullable|exists:portal_menus,id',
            'order' => 'integer'
        ]);

        PortalMenu::create($validated);
        return redirect()->back()->with('success', 'Menu created successfully!');
    }

    public function update(Request $request, PortalMenu $menu)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'portal_page_id' => 'nullable|exists:portal_pages,id',
            'parent_id' => 'nullable|exists:portal_menus,id',
            'order' => 'integer'
        ]);

        $menu->update($validated);
        return redirect()->back()->with('success', 'Menu updated successfully!');
    }

    public function destroy(PortalMenu $menu)
    {
        $menu->delete();
        return redirect()->back()->with('success', 'Menu deleted successfully!');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'menus' => 'required|array',
            'menus.*.id' => 'required|exists:portal_menus,id',
            'menus.*.parent_id' => 'nullable|exists:portal_menus,id',
            'menus.*.order' => 'required|integer',
        ]);

        \DB::transaction(function () use ($request) {
            foreach ($request->menus as $item) {
                PortalMenu::where('id', $item['id'])->update([
                    'parent_id' => $item['parent_id'],
                    'order' => $item['order']
                ]);
            }
        });

        \Illuminate\Support\Facades\Cache::forget('portal_menus');

        return redirect()->back()->with('success', 'Menu order updated successfully!');
    }
}
