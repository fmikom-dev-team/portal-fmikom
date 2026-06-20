<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\OrganizationInvitationMail;
use App\Models\Auth\AuthInvitation;
use App\Models\Module;
use App\Modules\WorkOs\Services\AuditLogger;
use App\Services\VirusScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrganizationsController extends Controller
{
    private const STORAGE_PREFIX = '/storage/';

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:20', 'unique:modules,code', 'regex:/^[A-Z0-9]+$/'],
            'description' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'max:2048'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');

            // Scan for virus signature using ClamAV
            $scanner = app(VirusScannerService::class);
            $scanResult = $scanner->scan($file);
            if (! $scanResult['safe']) {
                throw ValidationException::withMessages([
                    'logo_file' => $scanResult['reason'],
                ]);
            }

            $path = $file->store('portal/modules', 'public');
            $logoPath = self::STORAGE_PREFIX.$path;
        }

        $module = Module::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'is_active' => true,
            'logo_path' => $logoPath,
        ]);

        AuditLogger::log('organization.created', 'info', ['name' => $module->name], $module);

        return back()->with('success', "Organisasi '{$request->name}' berhasil dibuat.");
    }

    public function update(Request $request, Module $module)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
            'logo_file' => ['nullable', 'image', 'max:2048'],
        ]);

        $logoPath = $module->logo_path;
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');

            // Scan for virus signature using ClamAV
            $scanner = app(VirusScannerService::class);
            $scanResult = $scanner->scan($file);
            if (! $scanResult['safe']) {
                throw ValidationException::withMessages([
                    'logo_file' => $scanResult['reason'],
                ]);
            }

            // Delete old file if exists
            if ($module->logo_path) {
                $filePath = str_replace(self::STORAGE_PREFIX, '', $module->logo_path);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            $path = $file->store('portal/modules', 'public');
            $logoPath = self::STORAGE_PREFIX.$path;
        }

        $module->fill([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $module->is_active,
            'logo_path' => $logoPath,
        ]);
        $module->save();

        AuditLogger::log('organization.updated', 'info', ['name' => $module->name], $module);

        return back()->with('success', 'Organisasi berhasil diperbarui.');
    }

    public function destroy(Module $module)
    {
        $moduleName = $module->name;
        $module->userRoles()->{'delete'}();
        $module->{'delete'}();

        AuditLogger::log('organization.deleted', 'warning', ['name' => $moduleName], $module);

        return back()->with('success', "Organisasi '{$module->name}' berhasil dihapus.");
    }

    public function addRole(Request $request, int|string $moduleId)
    {
        $module = Module::findOrFail($moduleId);
        $request->validate(['role_id' => 'required|exists:roles,id']);

        if (! $module->roles()->where('role_id', '=', $request->role_id, 'and')->exists()) {
            $module->roles()->attach($request->role_id);
        }

        return back()->with('success', 'Role berhasil ditambahkan ke organisasi.');
    }

    public function removeRole(int|string $moduleId, int|string $roleId)
    {
        $module = Module::findOrFail($moduleId);

        if (! $module->roles()->where('roles.id', '=', $roleId, 'and')->exists()) {
            abort(404, 'Role tidak terasosiasi dengan modul ini.');
        }

        $module->roles()->detach($roleId);

        return back()->with('success', 'Role berhasil dikeluarkan dari organisasi.');
    }

    public function indexInvitations(Module $module)
    {
        $invitations = AuthInvitation::where('module_id', $module->id)
            ->latest('created_at')
            ->get();

        return response()->json([
            'invitations' => $invitations,
        ]);
    }

    public function sendInvitation(Request $r, Module $module)
    {
        $r->validate([
            'email' => 'required|email',
            'role' => 'required|string',
        ]);

        // Generate unique invitation token
        $token = Str::random(64);
        $expiresAt = now()->addDays(7);

        // Inviter name: authenticated user or fallback
        $invitedBy = auth()->user()?->name ?? config('app.name');

        // Persist the invitation record
        AuthInvitation::create([
            'module_id' => $module->id,
            'email' => $r->email,
            'role' => ucfirst($r->role),
            'status' => 'Pending',
            'token' => $token,
            'invited_by' => $invitedBy,
            'organization_name' => $module->name,
            'expires_at' => $expiresAt,
        ]);

        cache()->put("seeded_invites_for_module_{$module->id}", true, now()->addYears(1));

        // Build accept URL pointing to the accept invitation page
        $acceptUrl = url("/invitations/accept/{$token}");

        // Send real email
        try {
            Mail::to($r->email)->send(new OrganizationInvitationMail(
                invitedEmail: $r->email,
                organizationName: $module->name,
                role: ucfirst($r->role),
                invitedBy: $invitedBy,
                acceptUrl: $acceptUrl,
                expiresAt: $expiresAt->format('d M Y, H:i').' WIB',
            ));
        } catch (\Exception $e) {
            \Log::error('Failed to send invitation email: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Invitation saved but email failed to send. Please check your SMTP configuration.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Invitation email sent to {$r->email} successfully.",
        ]);
    }

    public function clearInvitations(Module $module)
    {
        AuthInvitation::where('module_id', $module->id)->delete();
        cache()->put("seeded_invites_for_module_{$module->id}", true, now()->addYears(1));

        return response()->json([
            'success' => true,
            'message' => 'Invitation history cleared successfully.',
        ]);
    }

    public function deleteInvitation(Module $module, $invitation)
    {
        AuthInvitation::where('module_id', $module->id)
            ->where('id', $invitation)
            ->delete();
        cache()->put("seeded_invites_for_module_{$module->id}", true, now()->addYears(1));

        return response()->json([
            'success' => true,
            'message' => 'Invitation removed successfully.',
        ]);
    }
}
