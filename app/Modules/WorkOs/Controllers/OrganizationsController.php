<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\AuthInvitation;
use App\Mail\OrganizationInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrganizationsController extends Controller
{
    public function store(Request $r) 
    { 
        return app(DashboardController::class)->storeModule($r); 
    }

    public function update(Request $r, Module $module) 
    { 
        return app(DashboardController::class)->updateModule($r, $module); 
    }

    public function destroy(Module $module) 
    { 
        return app(DashboardController::class)->destroyModule($module); 
    }

    public function addRole(Request $r, $m) 
    { 
        return app(DashboardController::class)->addModuleRoleMapping($r, $m); 
    }

    public function removeRole($m, $r) 
    { 
        return app(DashboardController::class)->removeModuleRoleMapping($m, $r); 
    }

    public function indexInvitations(Module $module)
    {
        $seededKey = "seeded_invites_for_module_{$module->id}";
        $count = AuthInvitation::where('module_id', $module->id)->count();
        if ($count === 0 && !cache()->has($seededKey)) {
            AuthInvitation::create([
                'module_id' => $module->id,
                'email' => 'iha70741@gmail.com',
                'role' => 'Admin',
                'status' => 'Accepted',
                'created_at' => '2026-05-17 20:15:00',
            ]);
            cache()->put($seededKey, true, now()->addYears(1));
        }

        $invitations = AuthInvitation::where('module_id', $module->id)
            ->latest('created_at')
            ->get();

        return response()->json([
            'invitations' => $invitations
        ]);
    }

    public function sendInvitation(Request $r, Module $module)
    {
        $r->validate([
            'email' => 'required|email',
            'role'  => 'required|string',
        ]);

        // Generate unique invitation token
        $token     = Str::random(64);
        $expiresAt = now()->addDays(7);

        // Inviter name: authenticated user or fallback
        $invitedBy = auth()->user()?->name ?? config('app.name');

        // Persist the invitation record
        AuthInvitation::create([
            'module_id'         => $module->id,
            'email'             => $r->email,
            'role'              => ucfirst($r->role),
            'status'            => 'Pending',
            'token'             => $token,
            'invited_by'        => $invitedBy,
            'organization_name' => $module->name,
            'expires_at'        => $expiresAt,
        ]);

        cache()->put("seeded_invites_for_module_{$module->id}", true, now()->addYears(1));

        // Build accept URL pointing to the accept invitation page
        $acceptUrl = url("/invitations/accept/{$token}");

        // Send real email
        try {
            Mail::to($r->email)->send(new OrganizationInvitationMail(
                invitedEmail:     $r->email,
                organizationName: $module->name,
                role:             ucfirst($r->role),
                invitedBy:        $invitedBy,
                acceptUrl:        $acceptUrl,
                expiresAt:        $expiresAt->format('d M Y, H:i') . ' WIB',
            ));
        } catch (\Exception $e) {
            \Log::error('Failed to send invitation email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Invitation saved but email failed to send. Please check your SMTP configuration.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Invitation email sent to {$r->email} successfully."
        ]);
    }

    public function clearInvitations(Module $module)
    {
        AuthInvitation::where('module_id', $module->id)->delete();
        cache()->put("seeded_invites_for_module_{$module->id}", true, now()->addYears(1));

        return response()->json([
            'success' => true,
            'message' => 'Invitation history cleared successfully.'
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
            'message' => 'Invitation removed successfully.'
        ]);
    }
}
