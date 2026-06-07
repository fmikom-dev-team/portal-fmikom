<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;

use App\Events\PagiMessageSent;
use App\Events\PagiMessagesRead;
use App\Events\PagiMessageDeleted;
use App\Events\PagiMessageEdited;
use App\Events\PagiMessageReacted;
use App\Models\Pagi\PagiMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PagiChatController extends Controller
{
    /**
     * Show the messages page with the list of conversations.
     * A conversation is any user the auth user has exchanged messages with.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. If 'chat' query param exists, ensure it is added to active_chats
        $chatPartnerId = $request->query('chat');
        if ($chatPartnerId) {
            $partnerId = (int)$chatPartnerId;
            if ($partnerId > 0 && User::where('id', $partnerId)->exists()) {
                $metadata = $user->metadata ?? [];
                $activeChats = $metadata['active_chats'] ?? [];
                if (!in_array($partnerId, $activeChats)) {
                    $activeChats[] = $partnerId;
                    $metadata['active_chats'] = $activeChats;
                    $user->update(['metadata' => $metadata]);
                    $user->refresh();
                }
            }
        }

        // 2. Fetch partner IDs from message history (excluding cleared messages)
        $clearedChats = $user->metadata['cleared_chats'] ?? [];
        $partnerIdsQuery = PagiMessage::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            });

        if (!empty($clearedChats)) {
            $partnerIdsQuery->whereNot(function ($q) use ($clearedChats, $user) {
                foreach ($clearedChats as $partnerId => $clearedAt) {
                    $convId = PagiMessage::conversationId($user->id, (int)$partnerId);
                    $q->orWhere(function ($sq) use ($convId, $clearedAt) {
                        $sq->where('conversation_id', $convId)
                           ->where('created_at', '<=', $clearedAt);
                    });
                }
            });
        }

        $messagePartnerIds = $partnerIdsQuery->get()
            ->map(fn ($msg) => $msg->sender_id === $user->id ? $msg->receiver_id : $msg->sender_id)
            ->unique()
            ->values()
            ->toArray();

        // 3. Combine with metadata active_chats
        $activeChatsList = $user->metadata['active_chats'] ?? [];
        $allPartnerIds = array_values(array_unique(array_merge($messagePartnerIds, $activeChatsList)));

        // ── Database Query Optimizations (N+1 Prevention) ─────────────────────
        // 1. Fetch unread counts in bulk group by conversation_id (excluding cleared messages)
        $unreadCountsQuery = PagiMessage::where('receiver_id', $user->id)
            ->whereNull('read_at');

        if (!empty($clearedChats)) {
            $unreadCountsQuery->whereNot(function ($q) use ($clearedChats, $user) {
                foreach ($clearedChats as $partnerId => $clearedAt) {
                    $convId = PagiMessage::conversationId($user->id, (int)$partnerId);
                    $q->orWhere(function ($sq) use ($convId, $clearedAt) {
                        $sq->where('conversation_id', $convId)
                           ->where('created_at', '<=', $clearedAt);
                    });
                }
            });
        }

        $unreadCounts = $unreadCountsQuery->groupBy('conversation_id')
            ->select('conversation_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->pluck('count', 'conversation_id');

        // 2. Fetch last messages in bulk using groupwise max ID lookup (excluding cleared messages)
        $userId = $user->id;
        $lastMessagesQuery = PagiMessage::whereIn('id', function ($query) use ($userId, $clearedChats) {
            $query->select(\Illuminate\Support\Facades\DB::raw('MAX(id)'))
                ->from('pagi_messages')
                ->where(function ($q) use ($userId) {
                    $q->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
                });

            if (!empty($clearedChats)) {
                $query->whereNot(function ($q) use ($clearedChats, $userId) {
                    foreach ($clearedChats as $partnerId => $clearedAt) {
                        $convId = PagiMessage::conversationId($userId, (int)$partnerId);
                        $q->orWhere(function ($sq) use ($convId, $clearedAt) {
                            $sq->where('conversation_id', $convId)
                               ->where('created_at', '<=', $clearedAt);
                        });
                    }
                });
            }

            $query->groupBy('conversation_id');
        });

        $lastMessages = $lastMessagesQuery->get()->keyBy('conversation_id');

        // 4. Retrieve the User records for these partners
        $conversations = User::whereIn('id', $allPartnerIds)
            ->select('id', 'name', 'foto_path', 'last_seen_at', 'metadata')
            ->get()
            ->map(function (User $partner) use ($user, $unreadCounts, $lastMessages) {
                $convId = PagiMessage::conversationId($user->id, $partner->id);
                $lastMsg = $lastMessages->get($convId);
                $unread = $unreadCounts->get($convId, 0);

                $blockedByMe = in_array((int)$partner->id, $user->metadata['blocked_users'] ?? []);
                $blockedMe = in_array((int)$user->id, $partner->metadata['blocked_users'] ?? []);
                
                $pinnedChats = $user->metadata['pinned_chats'] ?? [];
                $archivedChats = $user->metadata['archived_chats'] ?? [];
                $unreadChats = $user->metadata['unread_chats'] ?? [];

                $isPinned = in_array((int)$partner->id, $pinnedChats);
                $isArchived = in_array((int)$partner->id, $archivedChats);
                $isManualUnread = in_array((int)$partner->id, $unreadChats);

                // Skip this partner if they have no visible messages (all deleted for me or cleared)
                // AND they are not in active_chats
                if (!$lastMsg && !in_array((int)$partner->id, ($user->metadata['active_chats'] ?? []))) {
                    return null;
                }

                $lastMessageIsDeleted = $lastMsg ? (bool)$lastMsg->is_deleted : false;
                $lastMessageIsDeletedForMe = false;
                if ($lastMsg && $lastMsg->deleted_for) {
                    $deletedFor = array_map('intval', $lastMsg->deleted_for);
                    if (in_array((int)$user->id, $deletedFor, true)) {
                        $lastMessageIsDeletedForMe = true;
                    }
                }

                return [
                    'id'        => $partner->id,
                    'name'      => $partner->name,
                    'foto_path' => $partner->foto_path,
                    'last_seen_at' => $partner->last_seen_at?->toISOString(),
                    'metadata'  => $partner->metadata,
                    'last_message' => ($lastMessageIsDeleted || $lastMessageIsDeletedForMe) ? '' : $lastMsg?->body,
                    'last_message_at' => $lastMsg?->created_at?->toISOString(),
                    'last_message_sender_id' => $lastMsg?->sender_id,
                    'last_message_read_at' => $lastMsg?->read_at?->toISOString(),
                    'unread_count' => $unread,
                    'conversation_id' => $convId,
                    'is_blocked_by_me' => $blockedByMe,
                    'has_blocked_me' => $blockedMe,
                    'is_pinned' => $isPinned,
                    'is_archived' => $isArchived,
                    'is_manual_unread' => $isManualUnread,
                    'last_message_is_deleted' => $lastMessageIsDeleted,
                    'last_message_is_deleted_for_me' => $lastMessageIsDeletedForMe,
                    'last_message_id' => $lastMsg?->id,
                ];
            })->filter()->values();

        return Inertia::render('Modules/Pagi/User/Messages', [
            'conversations' => $conversations,
            'authUser' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'foto_path' => $user->foto_path ?? null,
                'metadata' => $user->metadata,
            ],
        ]);
    }

    /**
     * Return message history with a specific user (JSON for SPA fetch).
     */
    public function show(Request $request, User $partner)
    {
        $user = Auth::user();
        if ((int)$partner->id === (int)$user->id) {
            abort(403, 'Anda tidak dapat mengirim pesan kepada diri sendiri.');
        }

        $cursorId = $request->query('cursor_id');

        $query = PagiMessage::conversation($user->id, $partner->id)
            ->with(['sender:id,name,foto_path', 'parent.sender:id,name']);

        if ($cursorId) {
            $query->where('id', '<', (int)$cursorId);
        }

        // Apply cleared_chats check
        $clearedChats = $user->metadata['cleared_chats'] ?? [];
        if (isset($clearedChats[$partner->id])) {
            $query->where('created_at', '>', $clearedChats[$partner->id]);
        }

        // Do NOT call ->visibleTo($user->id) so that deleted-for-me messages are still loaded as placeholders
        $messages = $query->orderBy('id', 'desc')
            ->take(30)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($msg) use ($user) {
                $isDeletedForMe = false;
                if ($msg->deleted_for) {
                    $deletedFor = array_map('intval', $msg->deleted_for);
                    if (in_array((int)$user->id, $deletedFor, true)) {
                        $isDeletedForMe = true;
                    }
                }

                $isDeleted = (bool) $msg->is_deleted;

                // Determine if parent is deleted/deleted for me
                $parentBody = null;
                if ($msg->parent) {
                    $parentIsDeletedForMe = false;
                    if ($msg->parent->deleted_for) {
                        $parentDeletedFor = array_map('intval', $msg->parent->deleted_for);
                        if (in_array((int)$user->id, $parentDeletedFor, true)) {
                            $parentIsDeletedForMe = true;
                        }
                    }
                    if ($msg->parent->is_deleted || $parentIsDeletedForMe) {
                        $parentBody = '';
                    } else {
                        $parentBody = $msg->parent->body;
                    }
                }

                return [
                    'id'         => $msg->id,
                    'sender_id'  => $msg->sender_id,
                    'body'       => ($isDeleted || $isDeletedForMe) ? '' : $msg->body,
                    'is_deleted' => $isDeleted,
                    'is_deleted_for_me' => $isDeletedForMe,
                    'edited_at'  => $msg->edited_at?->toISOString(),
                    'parent_id'  => $msg->parent_id,
                    'parent'     => $msg->parent ? [
                        'id' => $msg->parent->id,
                        'body' => $parentBody,
                        'sender' => [
                            'id' => $msg->parent->sender->id,
                            'name' => $msg->parent->sender->name,
                        ],
                    ] : null,
                    'read_at'    => $msg->read_at ? $msg->read_at->toISOString() : null,
                    'created_at' => $msg->created_at->toISOString(),
                    'reactions'  => $msg->reactions ?? [],
                    'sender' => [
                        'id'        => $msg->sender->id,
                        'name'      => $msg->sender->name,
                        'foto_path' => $msg->sender->foto_path,
                    ],
                ];
            });

        // Mark incoming messages as read (excluding cleared and deleted for me)
        $now = now();
        $unreadQuery = PagiMessage::where('conversation_id', PagiMessage::conversationId($user->id, $partner->id))
            ->where('receiver_id', $user->id)
            ->whereNull('read_at');

        if (isset($clearedChats[$partner->id])) {
            $unreadQuery->where('created_at', '>', $clearedChats[$partner->id]);
        }

        $updatedCount = $unreadQuery->update(['read_at' => $now]);

        // Clear manual unread status in metadata on the server
        $metadata = $user->metadata ?? [];
        $unreadChats = $metadata['unread_chats'] ?? [];
        if (in_array((int)$partner->id, $unreadChats)) {
            $unreadChats = array_values(array_diff($unreadChats, [(int)$partner->id]));
            $metadata['unread_chats'] = $unreadChats;
            $user->update(['metadata' => $metadata]);
        }

        if ($updatedCount > 0) {
            $conversationId = PagiMessage::conversationId($user->id, $partner->id);
            broadcast(new \App\Events\PagiMessagesRead($conversationId, $user->id, $now->toISOString(), (int)$partner->id))->toOthers();
            $myUnreadCount = PagiMessage::where('receiver_id', $user->id)->whereNull('read_at')->count();
            broadcast(new \App\Events\PagiUnreadCountUpdated($user->id, $myUnreadCount));
        }

        $blockedByMe = in_array((int)$partner->id, $user->metadata['blocked_users'] ?? []);
        $blockedMe = in_array((int)$user->id, $partner->metadata['blocked_users'] ?? []);

        return response()->json([
            'messages' => $messages,
            'partner'  => [
                'id'        => $partner->id,
                'name'      => $partner->name,
                'foto_path' => $partner->foto_path ?? null,
                'last_seen_at' => $partner->last_seen_at?->toISOString(),
                'metadata'  => $partner->metadata,
            ],
            'conversation_id' => PagiMessage::conversationId($user->id, $partner->id),
            'is_blocked_by_me' => $blockedByMe,
            'has_blocked_me' => $blockedMe,
        ]);
    }

    /**
     * Send a new message. Validates, persists, broadcasts.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id', 'different:' . Auth::id()],
            'parent_id'   => ['nullable', 'integer', 'exists:pagi_messages,id'],
            'body'        => ['required', 'string', 'max:4000'],
        ]);

        $user = Auth::user();
        $receiverId = (int) $request->receiver_id;
        $receiver = User::findOrFail($receiverId);

        // Check block status
        $userBlocked = $user->metadata['blocked_users'] ?? [];
        if (in_array($receiverId, $userBlocked)) {
            return response()->json(['error' => 'Anda telah memblokir pengguna ini.'], 403);
        }

        $receiverBlocked = $receiver->metadata['blocked_users'] ?? [];
        if (in_array($user->id, $receiverBlocked)) {
            return response()->json(['error' => 'Anda telah diblokir oleh pengguna ini.'], 403);
        }

        $message = PagiMessage::create([
            'conversation_id' => PagiMessage::conversationId($user->id, $receiverId),
            'sender_id'       => $user->id,
            'receiver_id'     => $receiverId,
            'parent_id'       => $request->parent_id,
            'body'            => trim($request->body),
            'reactions'       => [],
        ]);

        $message->load(['sender:id,name,foto_path', 'parent.sender:id,name']);

        // Ensure receiver is in sender's active chats metadata
        $senderMetadata = $user->metadata ?? [];
        $senderActiveChats = $senderMetadata['active_chats'] ?? [];
        if (!in_array($receiverId, $senderActiveChats)) {
            $senderActiveChats[] = $receiverId;
            $senderMetadata['active_chats'] = $senderActiveChats;
            $user->update(['metadata' => $senderMetadata]);
        }

        // Ensure sender is in receiver's active chats metadata
        $receiverMetadata = $receiver->metadata ?? [];
        $receiverActiveChats = $receiverMetadata['active_chats'] ?? [];
        if (!in_array($user->id, $receiverActiveChats)) {
            $receiverActiveChats[] = $user->id;
            $receiverMetadata['active_chats'] = $receiverActiveChats;
            $receiver->update(['metadata' => $receiverMetadata]);
        }

        broadcast(new PagiMessageSent($message))->toOthers();

        // Broadcast receiver's updated unread messages count
        $receiverUnreadCount = PagiMessage::where('receiver_id', $receiverId)->whereNull('read_at')->count();
        broadcast(new \App\Events\PagiUnreadCountUpdated($receiverId, $receiverUnreadCount))->toOthers();

        return response()->json([
            'id'         => $message->id,
            'sender_id'  => $message->sender_id,
            'body'       => $message->body,
            'parent_id'  => $message->parent_id,
            'parent'     => $message->parent ? [
                'id' => $message->parent->id,
                'body' => $message->parent->body,
                'sender' => [
                    'id' => $message->parent->sender->id,
                    'name' => $message->parent->sender->name,
                ],
            ] : null,
            'read_at'    => null,
            'created_at' => $message->created_at->toISOString(),
            'reactions'  => [],
            'sender' => [
                'id'        => $message->sender->id,
                'name'      => $message->sender->name,
                'foto_path' => $message->sender->foto_path,
            ],
        ], 201);
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
        $partnerId = (int) $request->partner_id;
        $conversationId = PagiMessage::conversationId($user->id, $partnerId);
        $now = now();

        $updatedCount = PagiMessage::where('conversation_id', $conversationId)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => $now]);

        // Clear manual unread status in metadata on the server
        $metadata = $user->metadata ?? [];
        $unreadChats = $metadata['unread_chats'] ?? [];
        if (in_array($partnerId, $unreadChats)) {
            $unreadChats = array_values(array_diff($unreadChats, [$partnerId]));
            $metadata['unread_chats'] = $unreadChats;
            $user->update(['metadata' => $metadata]);
        }

        if ($updatedCount > 0) {
            broadcast(new PagiMessagesRead($conversationId, $user->id, $now->toISOString(), $partnerId))->toOthers();
            $myUnreadCount = PagiMessage::where('receiver_id', $user->id)->whereNull('read_at')->count();
            broadcast(new \App\Events\PagiUnreadCountUpdated($user->id, $myUnreadCount));
        }

        return response()->json([
            'success' => true,
            'read_at' => $now->toISOString()
        ]);
    }

    /**
     * Save the user's E2EE public and private keys in metadata.
     */
    public function updatePublicKey(Request $request)
    {
        $request->validate([
            'public_key' => ['required', 'array'],
            'private_key' => ['nullable', 'array'],
        ]);

        $user = Auth::user();
        $metadata = $user->metadata ?? [];
        $metadata['pagi_e2e_pubkey'] = $request->public_key;
        if ($request->has('private_key')) {
            $metadata['pagi_e2e_privkey'] = $request->private_key;
        }

        $user->update(['metadata' => $metadata]);

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
        $emoji = $request->emoji;
        $reactions = $message->reactions ?? [];

        if (isset($reactions[$emoji])) {
            $userIds = $reactions[$emoji];
            if (in_array($user->id, $userIds)) {
                $reactions[$emoji] = array_values(array_filter($userIds, fn($id) => $id !== $user->id));
                if (empty($reactions[$emoji])) {
                    unset($reactions[$emoji]);
                }
            } else {
                $reactions[$emoji][] = $user->id;
            }
        } else {
            $reactions[$emoji] = [$user->id];
        }

        $message->update(['reactions' => $reactions]);

        broadcast(new PagiMessageReacted($message->id, $message->conversation_id, $reactions))->toOthers();

        return response()->json([
            'success' => true,
            'reactions' => $reactions,
        ]);
    }

    /**
     * Edit pesan — hanya sender, maksimal 20 menit setelah terkirim.
     */
    public function update(Request $request, PagiMessage $message)
    {
        $user = Auth::user();

        // Hanya sender yang boleh edit
        if ((int)$message->sender_id !== (int)$user->id) {
            return response()->json(['error' => 'Unauthorized. Hanya pengirim yang dapat mengedit pesan.'], 403);
        }

        // Pesan sudah dihapus, tidak bisa diedit
        if ($message->is_deleted) {
            return response()->json(['error' => 'Pesan yang sudah dihapus tidak dapat diedit.'], 422);
        }

        // Cek batas waktu 20 menit
        $minutesSinceSent = $message->created_at->diffInMinutes(now());
        if ($minutesSinceSent > 20) {
            return response()->json(['error' => 'Pesan hanya dapat diedit dalam 20 menit setelah dikirim.'], 422);
        }

        $request->validate(['body' => 'required|string|max:5000']);

        $message->update([
            'body'      => $request->body,
            'edited_at' => now(),
        ]);

        broadcast(new PagiMessageEdited(
            $message->id,
            $message->conversation_id,
            $message->body,
            $message->edited_at->toISOString()
        ))->toOthers();

        return response()->json([
            'success'   => true,
            'body'      => $message->body,
            'edited_at' => $message->edited_at->toISOString(),
        ]);
    }

    /**
     * Hapus pesan — WhatsApp style.
     * - delete_type=for_everyone: Hanya sender yang boleh. Meninggalkan placeholder "Pesan ini telah dihapus" di kedua sisi.
     * - delete_type=for_me: Siapapun boleh. Hanya menambahkan user ke deleted_for, pihak lain tetap lihat pesan asli.
     */
    public function destroy(Request $request, PagiMessage $message)
    {
        $request->validate([
            'delete_type' => ['required', 'in:for_me,for_everyone'],
        ]);

        $user       = Auth::user();
        $userId     = (int) $user->id;
        $senderId   = (int) $message->sender_id;
        $receiverId = (int) $message->receiver_id;

        // Hanya sender atau receiver yang boleh menghapus
        if ($userId !== $senderId && $userId !== $receiverId) {
            return response()->json(['error' => 'Unauthorized. Anda bukan bagian dari percakapan ini.'], 403);
        }

        $messageId      = $message->id;
        $conversationId = $message->conversation_id;
        $deleteType     = $request->delete_type;

        if ($deleteType === 'for_everyone') {
            // Hanya sender yang boleh hapus untuk semua orang
            if ($userId !== $senderId) {
                return response()->json(['error' => 'Hanya pengirim yang dapat menghapus pesan untuk semua orang.'], 403);
            }

            // Tandai is_deleted → tampil placeholder "Pesan ini telah dihapus" di kedua sisi
            $message->update([
                'body'       => '',
                'is_deleted' => true,
                'edited_at'  => null,
            ]);

            // Broadcast ke partner agar ikut update tampilannya
            broadcast(new PagiMessageDeleted($messageId, $conversationId, $senderId, $receiverId))->toOthers();
        } else {
            // delete_type=for_me → tambah ke deleted_for saja, pihak lain tidak terpengaruh
            $deletedFor = array_map('intval', $message->deleted_for ?? []);
            if (!in_array($userId, $deletedFor, true)) {
                $deletedFor[] = $userId;
                $message->update(['deleted_for' => $deletedFor]);
            }
        }

        return response()->json([
            'success'     => true,
            'message_id'  => $messageId,
            'delete_type' => $deleteType,
        ]);
    }

    /**
     * Clear all messages in a conversation with a partner (Delete for me style).
     */
    public function clearChat(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = Auth::user();
        $partnerId = (int) $request->partner_id;

        // 1. Save cleared chat timestamp in user's metadata
        $metadata = $user->metadata ?? [];
        $clearedChats = $metadata['cleared_chats'] ?? [];
        $clearedChats[$partnerId] = now()->toISOString();
        $metadata['cleared_chats'] = $clearedChats;
        $user->update(['metadata' => $metadata]);

        // 2. Mark incoming messages as read
        PagiMessage::conversation($user->id, $partnerId)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Block a user.
     */
    public function blockUser(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id', 'different:' . Auth::id()],
        ]);

        $user = Auth::user();
        $partnerId = (int) $request->partner_id;
        $metadata = $user->metadata ?? [];
        $blocked = $metadata['blocked_users'] ?? [];

        if (!in_array($partnerId, $blocked)) {
            $blocked[] = $partnerId;
        }

        $metadata['blocked_users'] = array_values(array_unique($blocked));
        $user->update(['metadata' => $metadata]);

        return response()->json(['success' => true, 'metadata' => $metadata]);
    }

    /**
     * Unblock a user.
     */
    public function unblockUser(Request $request)
    {
        $request->validate([
            'partner_id' => ['required', 'integer', 'exists:users,id', 'different:' . Auth::id()],
        ]);

        $user = Auth::user();
        $partnerId = (int) $request->partner_id;
        $metadata = $user->metadata ?? [];
        $blocked = $metadata['blocked_users'] ?? [];

        $blocked = array_filter($blocked, fn($id) => (int)$id !== $partnerId);

        $metadata['blocked_users'] = array_values($blocked);
        $user->update(['metadata' => $metadata]);

        return response()->json(['success' => true, 'metadata' => $metadata]);
    }

    /**
     * Get list of potential contacts to start a new chat.
     */
    public function contacts()
    {
        $user = Auth::user();
        // Get all users in PAGI module except the auth user
        $pagiModule = \App\Models\Module::where('code', 'PAGI')->first();
        if (!$pagiModule) {
            return response()->json([]);
        }

        $userIds = \App\Models\UserModuleRole::where('module_id', $pagiModule->id)
            ->where('user_id', '!=', $user->id)
            ->where('is_active', true)
            ->pluck('user_id')
            ->toArray();

        // Filter: only users the auth user is following (stored in metadata['following'])
        $followingIds = $user->metadata['following'] ?? [];
        $allowedIds = array_values(array_intersect($userIds, $followingIds));

        $contacts = User::whereIn('id', $allowedIds)
            ->select('id', 'name', 'foto_path')
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'foto_path' => $u->foto_path,
                'avatar' => $u->foto_path 
                    ? (str_starts_with($u->foto_path, 'http') ? $u->foto_path : '/storage/' . $u->foto_path)
                    : 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($u->name) . '&backgroundColor=3b82f6',
            ]);

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
        $partnerId = (int) $request->partner_id;
        $metadata = $user->metadata ?? [];
        $pinned = $metadata['pinned_chats'] ?? [];

        if (in_array($partnerId, $pinned)) {
            $pinned = array_values(array_diff($pinned, [$partnerId]));
            $isPinned = false;
        } else {
            $pinned[] = $partnerId;
            $isPinned = true;
        }

        $metadata['pinned_chats'] = array_values(array_unique($pinned));
        $user->update(['metadata' => $metadata]);

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
        $partnerId = (int) $request->partner_id;
        $metadata = $user->metadata ?? [];
        $archived = $metadata['archived_chats'] ?? [];

        if (in_array($partnerId, $archived)) {
            $archived = array_values(array_diff($archived, [$partnerId]));
            $isArchived = false;
        } else {
            $archived[] = $partnerId;
            $isArchived = true;
        }

        $metadata['archived_chats'] = array_values(array_unique($archived));
        $user->update(['metadata' => $metadata]);

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
        $partnerId = (int) $request->partner_id;
        $metadata = $user->metadata ?? [];
        
        $convId = PagiMessage::conversationId($user->id, $partnerId);
        $hasUnread = PagiMessage::where('conversation_id', $convId)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->exists();

        $unreadChats = $metadata['unread_chats'] ?? [];

        if ($hasUnread || in_array($partnerId, $unreadChats)) {
            // Mark all as read
            $now = now();
            PagiMessage::where('conversation_id', $convId)
                ->where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => $now]);

            $unreadChats = array_values(array_diff($unreadChats, [$partnerId]));
            $status = 'read';

            broadcast(new \App\Events\PagiMessagesRead($convId, $user->id, $now->toISOString(), $partnerId))->toOthers();
        } else {
            // Mark as unread
            if (!in_array($partnerId, $unreadChats)) {
                $unreadChats[] = $partnerId;
            }
            $status = 'unread';
        }

        $metadata['unread_chats'] = array_values(array_unique($unreadChats));
        $user->update(['metadata' => $metadata]);

        // Broadcast updated unread count for this user
        $myUnreadCount = PagiMessage::where('receiver_id', $user->id)->whereNull('read_at')->count();
        broadcast(new \App\Events\PagiUnreadCountUpdated($user->id, $myUnreadCount));

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
        $partnerId = (int) $request->partner_id;
        $metadata = $user->metadata ?? [];

        // 1. Save cleared chat timestamp in user's metadata
        $clearedChats = $metadata['cleared_chats'] ?? [];
        $clearedChats[$partnerId] = now()->toISOString();
        $metadata['cleared_chats'] = $clearedChats;

        // 2. Mark incoming messages in this conversation as read so they don't count towards unread count
        PagiMessage::conversation($user->id, $partnerId)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // 3. Remove from active chats list
        $activeChats = $metadata['active_chats'] ?? [];
        $activeChats = array_values(array_diff($activeChats, [$partnerId]));
        $metadata['active_chats'] = $activeChats;

        // 4. Remove from pinned/archived/unread lists
        $pinned = $metadata['pinned_chats'] ?? [];
        $metadata['pinned_chats'] = array_values(array_diff($pinned, [$partnerId]));

        $archived = $metadata['archived_chats'] ?? [];
        $metadata['archived_chats'] = array_values(array_diff($archived, [$partnerId]));

        $unread = $metadata['unread_chats'] ?? [];
        $metadata['unread_chats'] = array_values(array_diff($unread, [$partnerId]));

        $user->update(['metadata' => $metadata]);

        // Broadcast updated unread count for this user
        $myUnreadCount = PagiMessage::where('receiver_id', $user->id)->whereNull('read_at')->count();
        broadcast(new \App\Events\PagiUnreadCountUpdated($user->id, $myUnreadCount));

        return response()->json(['success' => true]);
    }
}
