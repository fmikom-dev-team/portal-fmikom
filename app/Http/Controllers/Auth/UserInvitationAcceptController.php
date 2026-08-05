<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserInvitationAcceptController extends Controller
{
    public function show(Request $request): Response
    {
        $token = (string) $request->query('token', '');
        $invitation = UserInvitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (! $invitation || $invitation->isExpired()) {
            return Inertia::render('auth/AcceptInvitation', [
                'invalid' => true,
                'message' => 'Tautan undangan ini sudah tidak berlaku, kadaluarsa, atau telah digunakan.',
            ]);
        }

        return Inertia::render('auth/AcceptInvitation', [
            'invalid' => false,
            'token' => $token,
            'email' => $invitation->email,
            'first_name' => $invitation->first_name,
            'last_name' => $invitation->last_name,
            'user_type' => $invitation->user_type,
        ]);
    }

    public function accept(Request $request)
    {
        $request->merge([
            'name' => trim(strip_tags((string) $request->name)),
        ]);

        $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.\,\'\-]+$/'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'name.regex' => 'Nama lengkap hanya boleh berisi huruf, spasi, dan tanda baca standar.',
        ]);

        $invitation = UserInvitation::where('token', $request->token)
            ->where('status', 'pending')
            ->first();

        if (! $invitation || $invitation->isExpired()) {
            return back()->withErrors(['token' => 'Tautan undangan tidak valid atau kadaluarsa.']);
        }

        // Check if user with email already exists
        $user = User::where('email', $invitation->email)->first();
        if ($user) {
            $user->update([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'user_type' => $invitation->user_type,
                'status_approval' => 'activated',
                'is_active' => true,
                'email_verified_at' => $user->email_verified_at ?: now(),
                'password_changed_at' => now(),
            ]);

            $user->assignDefaultModuleRoles();

            $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);
            Auth::login($user);

            return redirect('/workos')->with('success', 'Akun Anda telah berhasil diaktifkan.');
        }

        $newUser = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'user_type' => $invitation->user_type,
            'status_approval' => 'activated',
            'is_active' => true,
            'email_verified_at' => now(),
            'password_changed_at' => now(),
        ]);

        $newUser->assignDefaultModuleRoles();

        $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);

        Auth::login($newUser);

        return redirect('/workos')->with('success', 'Selamat datang! Akun Anda berhasil diaktifkan.');
    }
}
