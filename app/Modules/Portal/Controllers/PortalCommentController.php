<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalComment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalCommentController extends Controller
{
    public function index()
    {
        $comments = PortalComment::with('post:id,title')->latest()->get();

        return Inertia::render('Modules/Portal/Admin/Comments', [
            'comments' => $comments,
        ]);
    }

    public function update(Request $request, PortalComment $comment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,spam',
        ]);

        $comment->update($validated);

        return redirect()->back()->with('success', 'Comment status updated!');
    }

    public function destroy(PortalComment $comment)
    {
        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted!');
    }
}
