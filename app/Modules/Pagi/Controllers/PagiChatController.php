<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiMessage;
use App\Models\Portal\PortalSetting;
use App\Models\User;
use App\Modules\Pagi\Services\ContentModerationService;
use App\Modules\Pagi\Services\PagiChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PagiChatController extends Controller
{
    protected PagiChatService $chatService;

    public function __construct(PagiChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Show the messages page with the list of conversations.
     */
    public function index(Request $request)
    {
        // Feature gate: check if chat is enabled
        $chatEnabled = filter_var(
            PortalSetting::query()->where('key', '=', 'pagi_enable_chat')->value('value') ?? 'true',
            FILTER_VALIDATE_BOOLEAN
        );

        if (! $chatEnabled) {
            return redirect()->route('module.pagi.dashboard')
                ->with('error', 'Fitur pesan langsung sedang dinonaktifkan oleh administrator.');
        }

        $user = Auth::user();
        $chatPartnerId = $request->query('chat') ? (int) $request->query('chat') : null;

        $formattedConversations = $this->chatService->getConversations($user, $chatPartnerId);

        // Baca kamus kata terlarang kustom dari DB untuk dikirim ke frontend (pre-scan E2EE)
        $customWordsJson = PortalSetting::query()->where('key', 'pagi_banned_words')->value('value');
        $customWords = [];
        if ($customWordsJson) {
            $decoded = json_decode($customWordsJson, true);
            if (is_array($decoded)) {
                $customWords = array_values(array_filter(array_map('strtolower', array_map('trim', $decoded))));
            }
        }

        return Inertia::render('Modules/Pagi/User/Messages', [
            'conversations' => $formattedConversations,
            'authUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'foto_path' => $user->foto_path ?? null,
                'metadata' => $user->metadata,
            ],
            'moderationCustomWords' => $customWords,
        ]);
    }

    /**
     * Return message history with a specific user (JSON for SPA fetch).
     */
    public function show(Request $request, User $partner)
    {
        $user = Auth::user();
        $cursorId = $request->query('cursor_id');

        $payload = $this->chatService->getConversationPayload($user, $partner, $cursorId);

        return response()->json($payload);
    }

    /**
     * Send a new message.
     */
    public function store(Request $request)
    {
        // Feature gate: reject message send if chat is disabled
        $chatEnabled = filter_var(
            PortalSetting::query()->where('key', '=', 'pagi_enable_chat')->value('value') ?? 'true',
            FILTER_VALIDATE_BOOLEAN
        );

        if (! $chatEnabled) {
            return response()->json([
                'message' => 'Fitur pesan langsung sedang dinonaktifkan oleh administrator.',
            ], 403);
        }

        $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id', 'different:'.Auth::id()],
            'parent_id' => ['nullable', 'integer', 'exists:pagi_messages,id'],
            'body' => ['required', 'string', 'max:4000'],
        ]);

        $user = Auth::user();

        $moderationService = app(ContentModerationService::class);
        $moderationMode = PortalSetting::query()->where('key', 'pagi_comment_censor_mode')->value('value') ?? 'reject';

        $scanResult = $moderationService->scan($request->body);

        if ($scanResult['is_flagged'] && $moderationMode === 'reject') {
            return response()->json([
                'message' => 'Pesan chat Anda ditolak otomatis oleh sistem karena terdeteksi memuat kata kasar, pelecehan, atau konten terlarang.',
            ], 422);
        }

        $finalBody = $scanResult['is_flagged'] ? $scanResult['censored_text'] : $request->body;

        $payload = $this->chatService->sendMessage(
            $user,
            (int) $request->receiver_id,
            $request->parent_id ? (int) $request->parent_id : null,
            $finalBody
        );

        return response()->json($payload, 201);
    }

    /**
     * Mark all messages in a conversation as read.
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $result = $this->chatService->markAsRead($user, (int) $request->partner_id);

        return response()->json($result);
    }

    /**
     * Save the user's E2EE public key in metadata.
     */
    public function updatePublicKey(Request $request)
    {
        $request->validate([
            'public_key' => ['required', 'array'],
        ]);

        $user = Auth::user();
        $this->chatService->updatePublicKey($user, $request->public_key);

        return response()->json(['success' => true]);
    }

    /**
     * Toggle reaction on a message.
     */
    public function react(Request $request, PagiMessage $message)
    {
        $request->validate([
            'emoji' => ['required', 'string', 'max:10'],
        ]);

        $user = Auth::user();
        $result = $this->chatService->reactToMessage($user, $message, $request->emoji);

        return response()->json($result);
    }

    /**
     * Edit message - sender only, max 20 mins after sending.
     */
    public function update(Request $request, PagiMessage $message)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $result = $this->chatService->editMessage($user, $message, $request->body);

        return response()->json($result);
    }

    /**
     * Delete message (WhatsApp style - for me or for everyone).
     */
    public function destroy(Request $request, PagiMessage $message)
    {
        $request->validate([
            'delete_type' => ['required', 'in:for_me,for_everyone'],
        ]);

        $user = Auth::user();
        $result = $this->chatService->deleteMessage($user, $message, $request->delete_type);

        return response()->json($result);
    }

    /**
     * Clear all messages in a conversation with a partner.
     */
    public function clearChat(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $this->chatService->clearChat($user, (int) $request->partner_id);

        return response()->json(['success' => true]);
    }

    /**
     * Block a user.
     */
    public function blockUser(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id', 'different:'.Auth::id()],
        ]);

        $user = Auth::user();
        $this->chatService->blockUser($user, (int) $request->partner_id);

        return response()->json(['success' => true]);
    }

    /**
     * Unblock a user.
     */
    public function unblockUser(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id', 'different:'.Auth::id()],
        ]);

        $user = Auth::user();
        $this->chatService->unblockUser($user, (int) $request->partner_id);

        return response()->json(['success' => true]);
    }

    /**
     * Get list of potential contacts to start a new chat.
     */
    public function contacts()
    {
        $user = Auth::user();
        $contacts = $this->chatService->getContacts($user);

        return response()->json($contacts);
    }

    /**
     * Pin / Unpin a conversation.
     */
    public function togglePinConversation(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $isPinned = $this->chatService->togglePin($user, (int) $request->partner_id);

        return response()->json(['success' => true, 'is_pinned' => $isPinned]);
    }

    /**
     * Archive / Unarchive a conversation.
     */
    public function toggleArchiveConversation(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $isArchived = $this->chatService->toggleArchive($user, (int) $request->partner_id);

        return response()->json(['success' => true, 'is_archived' => $isArchived]);
    }

    /**
     * Toggle read status (Mark as read / Mark as unread).
     */
    public function toggleReadConversation(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $status = $this->chatService->toggleRead($user, (int) $request->partner_id);

        return response()->json(['success' => true, 'status' => $status]);
    }

    /**
     * Delete entire conversation (WhatsApp style - Delete for me).
     */
    public function deleteConversation(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $this->chatService->deleteConversation($user, (int) $request->partner_id);

        return response()->json(['success' => true]);
    }
}
