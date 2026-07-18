<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthInvitation;
use Inertia\Inertia;

class InvitationAcceptController extends Controller
{
    /**
     * Show the invitation accept confirmation page.
     * [FIX CSRF GET state change] This view only presents details to the user.
     * No state changes are made.
     */
    public function show(string $token)
    {
        $invitation = AuthInvitation::where('token', $token)->first();

        if (! $invitation) {
            return Inertia::render('Invitations/InvalidToken', [
                'status' => 'invalid',
                'message' => 'This invitation link is invalid or has already been used.',
            ]);
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            return Inertia::render('Invitations/InvalidToken', [
                'status' => 'expired',
                'message' => 'This invitation link has expired.',
                'organization' => $invitation->organization_name,
            ]);
        }

        if ($invitation->status === 'Accepted') {
            return Inertia::render('Invitations/InvalidToken', [
                'status' => 'already_accepted',
                'message' => 'This invitation has already been accepted.',
                'organization' => $invitation->organization_name,
            ]);
        }

        return Inertia::render('Invitations/AcceptConfirm', [
            'token' => $token,
            'email' => $invitation->email,
            'organization' => $invitation->organization_name,
            'role' => $invitation->role,
            'invited_by' => $invitation->invited_by,
        ]);
    }

    /**
     * Consume the invitation and mark as Accepted.
     * [FIX CSRF GET state change] Executed strictly via POST.
     */
    public function accept(string $token)
    {
        $invitation = AuthInvitation::where('token', $token)->first();

        if (! $invitation) {
            abort(404, 'Invitation not found.');
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            abort(403, 'This invitation has expired.');
        }

        if ($invitation->status === 'Accepted') {
            abort(403, 'This invitation has already been accepted.');
        }

        // Safe database state modification
        $invitation->update(['status' => 'Accepted']);

        return Inertia::render('Invitations/AcceptSuccess', [
            'status' => 'accepted',
            'email' => $invitation->email,
            'organization' => $invitation->organization_name,
            'role' => $invitation->role,
            'invited_by' => $invitation->invited_by,
        ]);
    }
}
