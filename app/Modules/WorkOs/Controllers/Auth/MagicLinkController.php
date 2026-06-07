<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Stub — implement with your magic link service
 */
class MagicLinkController extends Controller
{
    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // TODO: Dispatch magic link via MagicLinkService
        return response()->json(['message' => 'Magic link sent if the email exists.']);
    }

    public function verify(Request $request)
    {
        // Signed URL is already validated by the 'signed' middleware
        // TODO: Exchange token → session via MagicLinkService
        return redirect()->route('dashboard')->with('success', 'Magic link verified.');
    }
}
