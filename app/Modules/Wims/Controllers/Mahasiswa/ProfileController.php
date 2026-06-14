<?php

namespace App\Modules\Wims\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Modules\Wims\Services\Mahasiswa\Profile\StudentProfilePageService;
use App\Modules\Wims\Services\Mahasiswa\Profile\StudentProfileUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        protected StudentProfilePageService $studentProfilePageService,
        protected StudentProfileUpdateService $studentProfileUpdateService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Mahasiswa/Profile/Index', $this->studentProfilePageService->build($request->user()));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'bio' => ['nullable', 'string', 'max:1500'],
            'website' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        $this->studentProfileUpdateService->update(
            $request->user(),
            $validated,
            $request->file('foto_profil'),
        );

        return back()->with('success', 'Profil WIMS berhasil diperbarui.');
    }
}
