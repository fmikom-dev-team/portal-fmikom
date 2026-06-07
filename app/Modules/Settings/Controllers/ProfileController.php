<?php

namespace App\Modules\Settings\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    use \App\Concerns\HandlesImageCompression;

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($request->hasFile('foto')) {
            $request->validate([
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);
            $path = $this->compressAndSaveImage($request->file('foto'), 'profile_photos', 400, 400, 80);
            if ($path) {
                if ($user->foto_path && !str_starts_with($user->foto_path, 'http')) {
                    $oldPath = storage_path('app/public/' . $user->foto_path);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $user->foto_path = $path;
            }
        } elseif ($request->has('avatar_url') && !empty($request->input('avatar_url'))) {
            if ($user->foto_path && !str_starts_with($user->foto_path, 'http')) {
                $oldPath = storage_path('app/public/' . $user->foto_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $user->foto_path = $request->input('avatar_url');
        } elseif ($request->boolean('remove_foto')) {
            if ($user->foto_path) {
                if (!str_starts_with($user->foto_path, 'http')) {
                    $oldPath = storage_path('app/public/' . $user->foto_path);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }
            $user->foto_path = null;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->user_type === 'super_admin') {
            return back()->withErrors(['password' => 'Akun Super Admin dilindungi dan tidak dapat dihapus.']);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Hapus remember cookie dari browser
        Cookie::queue(Cookie::forget(Auth::getRecallerName()));
        Cookie::queue(Cookie::forget('XSRF-TOKEN'));

        return redirect('/');
    }
}
