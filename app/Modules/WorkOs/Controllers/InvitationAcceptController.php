<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthInvitation;
use Inertia\Inertia;

class InvitationAcceptController extends Controller
{
    /**
     * Show the invitation accept page.
     * Token is validated and a success/expired/invalid page is displayed.
     */
    public function show(string $token)
    {
        $invitation = AuthInvitation::where('token', $token)->first();

        if (!$invitation) {
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

        // Mark as Accepted
        $invitation->update(['status' => 'Accepted']);

        return Inertia::render('Invitations/AcceptSuccess', [
            'status'       => 'accepted',
            'email'        => $invitation->email,
            'organization' => $invitation->organization_name,
            'role'         => $invitation->role,
            'invited_by'   => $invitation->invited_by,
        ]);
    }
}
