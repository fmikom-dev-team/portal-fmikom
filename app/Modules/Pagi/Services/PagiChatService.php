<?php

namespace App\Modules\Pagi\Services;

use App\Events\PagiMessageDeleted;
use App\Events\PagiMessageEdited;
use App\Events\PagiMessageReacted;
use App\Events\PagiMessageSent;
use App\Events\PagiMessagesRead;
use App\Events\PagiUnreadCountUpdated;
use App\Models\Module;
use App\Models\Pagi\PagiMessage;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PagiChatService
{
    /**
     * Get active conversations for a user.
     */
    public function getConversations(User $user, ?int $chatPartnerId = null): array
    {
        // 1. If 'chat' query param exists, ensure it is added to active_chats
        if ($chatPartnerId) {
            $partnerId = (int) $chatPartnerId;
            if ($partnerId > 0 && User::query()->where('id', '=', $partnerId, 'and')->exists()) {
                DB::table('pagi_active_chats')->insertOrIgnore([
                    'user_id' => $user->id,
                    'partner_id' => $partnerId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Fetch partner IDs from message history (excluding cleared messages)
        $clearedChats = DB::table('pagi_cleared_chats')
            ->where('user_id', $user->id)
            ->pluck('cleared_at', 'partner_id')
            ->toArray();

        $partnerIdsQuery = PagiMessage::query()->where(function ($q) use ($user) {
            $q->where('sender_id', '=', $user->id, 'and')
                ->orWhere('receiver_id', '=', $user->id);
        }, null, null, 'and');

        $this->applyClearedChatsFilter($partnerIdsQuery, $clearedChats, $user->id);

        $messagePartnerIds = $partnerIdsQuery->get()
            ->map(fn ($msg) => $msg->sender_id === $user->id ? $msg->receiver_id : $msg->sender_id)
            ->unique()
            ->values()
            ->toArray();

        // 3. Combine with active_chats relations
        $activeChatsList = DB::table('pagi_active_chats')
            ->where('user_id', $user->id)
            ->pluck('partner_id')
            ->toArray();

        $allPartnerIds = array_values(array_unique(array_merge($messagePartnerIds, $activeChatsList)));

        // ── Database Query Optimizations (N+1 Prevention) ─────────────────────
        // 1. Fetch unread counts in bulk group by conversation_id (excluding cleared messages)
        $unreadCountsQuery = PagiMessage::query()->where('receiver_id', '=', $user->id, 'and')
            ->whereNull('read_at', 'and', false);

        $this->applyClearedChatsFilter($unreadCountsQuery, $clearedChats, $user->id);

        $unreadCounts = $unreadCountsQuery->groupBy('conversation_id')
            ->select(['conversation_id', DB::raw('count(*) as count')])
            ->pluck('count', 'conversation_id');

        // 2. Fetch last messages in bulk using groupwise max ID lookup (excluding cleared messages)
        $userId = $user->id;
        $lastMessagesQuery = PagiMessage::query()->whereIn('id', function ($query) use ($userId, $clearedChats) {
            $query->select([DB::raw('MAX(id)')])
                ->from('pagi_messages')
                ->where(function ($q) use ($userId) {
                    $q->where('sender_id', '=', $userId, 'and')
                        ->orWhere('receiver_id', '=', $userId);
                }, null, null, 'and');

            $this->applyClearedChatsFilter($query, $clearedChats, $userId);

            $query->groupBy('conversation_id');
        }, 'and', false);

        $lastMessages = $lastMessagesQuery->get()->keyBy('conversation_id');

        // Bulk-fetch blocks, pinned, archived, unread arrays for performance
        $blockedByMeIds = DB::table('pagi_blocks')
            ->where('user_id', $user->id)
            ->pluck('blocked_id')
            ->toArray();

        $blockedMeIds = DB::table('pagi_blocks')
            ->where('blocked_id', $user->id)
            ->pluck('user_id')
            ->toArray();

        $pinnedChatsIds = DB::table('pagi_pinned_chats')
            ->where('user_id', $user->id)
            ->pluck('partner_id')
            ->toArray();

        $archivedChatsIds = DB::table('pagi_archived_chats')
            ->where('user_id', $user->id)
            ->pluck('partner_id')
            ->toArray();

        $unreadChatsIds = DB::table('pagi_unread_chats')
            ->where('user_id', $user->id)
            ->pluck('partner_id')
            ->toArray();

        // 4. Retrieve the User records for these partners
        $conversations = User::query()->whereIn('id', $allPartnerIds, 'and', false)
            ->select(['id', 'name', 'foto_path', 'last_seen_at', 'metadata'])
            ->get();

        return $this->formatConversations(
            $user,
            $conversations,
            $unreadCounts,
            $lastMessages,
            $blockedByMeIds,
            $blockedMeIds,
            $pinnedChatsIds,
            $archivedChatsIds,
            $unreadChatsIds,
            $activeChatsList
        )->toArray();
    }

    /**
     * Get conversation details and history messages.
     */
    public function getConversationPayload(User $user, User $partner, ?string $cursorId): array
    {
        if ((int) $partner->id === (int) $user->id) {
            abort(403, 'Anda tidak dapat mengirim pesan kepada diri sendiri.');
        }

        // Apply cleared_chats check
        $clearedChat = DB::table('pagi_cleared_chats')
            ->where('user_id', $user->id)
            ->where('partner_id', $partner->id)
            ->first();

        $query = $this->getMessagesQuery($user, $partner, $cursorId, $clearedChat);

        $messages = $query->orderBy('id', 'desc')
            ->take(30)
            ->get()
            ->reverse()
            ->values();

        $formattedMessages = $this->formatMessages($messages, $user);

        // Mark incoming messages as read (excluding cleared and deleted for me)
        $this->markMessagesAsRead($user, $partner, $clearedChat);

        $blockedByMe = DB::table('pagi_blocks')
            ->where('user_id', $user->id)
            ->where('blocked_id', $partner->id)
            ->exists();

        $blockedMe = DB::table('pagi_blocks')
            ->where('user_id', $partner->id)
            ->where('blocked_id', $user->id)
            ->exists();

        return [
            'messages' => $formattedMessages->toArray(),
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'foto_path' => $partner->foto_path ?? null,
                'last_seen_at' => $partner->last_seen_at?->toISOString(),
                'metadata' => $partner->metadata,
            ],
            'conversation_id' => PagiMessage::conversationId($user->id, $partner->id),
            'is_blocked_by_me' => $blockedByMe,
            'has_blocked_me' => $blockedMe,
        ];
    }

    /**
     * Send a new message.
     */
    public function sendMessage(User $user, int $receiverId, ?int $parentId, string $body): array
    {
        User::query()->findOrFail($receiverId);

        // Check block status
        $userBlocked = DB::table('pagi_blocks')
            ->where('user_id', $user->id)
            ->where('blocked_id', $receiverId)
            ->exists();
        if ($userBlocked) {
            abort(403, 'Anda telah memblokir pengguna ini.');
        }

        $receiverBlocked = DB::table('pagi_blocks')
            ->where('user_id', $receiverId)
            ->where('blocked_id', $user->id)
            ->exists();
        if ($receiverBlocked) {
            abort(403, 'Anda telah diblokir oleh pengguna ini.');
        }

        $message = DB::transaction(function () use ($user, $receiverId, $parentId, $body) {
            $msg = PagiMessage::create([
                'conversation_id' => PagiMessage::conversationId($user->id, $receiverId),
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'parent_id' => $parentId,
                'body' => trim($body),
                'reactions' => [],
            ]);

            DB::table('pagi_active_chats')->insertOrIgnore([
                'user_id' => $user->id,
                'partner_id' => $receiverId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('pagi_active_chats')->insertOrIgnore([
                'user_id' => $receiverId,
                'partner_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $msg;
        });

        $message->load(['sender:id,name,foto_path', 'parent.sender:id,name']);

        broadcast(new PagiMessageSent($message))->toOthers();

        // Broadcast receiver's updated unread messages count
        $receiverUnreadCount = PagiMessage::query()->where('receiver_id', '=', $receiverId, 'and')->whereNull('read_at', 'and', false)->count('*');
        broadcast(new PagiUnreadCountUpdated($receiverId, $receiverUnreadCount))->toOthers();

        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'body' => $message->body,
            'parent_id' => $message->parent_id,
            'parent' => $message->parent ? [
                'id' => $message->parent->id,
                'body' => $message->parent->body,
                'sender' => [
                    'id' => $message->parent->sender->id,
                    'name' => $message->parent->sender->name,
                ],
            ] : null,
            'read_at' => null,
            'created_at' => $message->created_at->toISOString(),
            'reactions' => [],
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'foto_path' => $message->sender->foto_path,
            ],
        ];
    }

    /**
     * Mark all messages in a conversation as read.
     */
    public function markAsRead(User $user, int $partnerId): array
    {
        $conversationId = PagiMessage::conversationId($user->id, $partnerId);
        $now = now();

        $updatedCount = PagiMessage::query()->where('conversation_id', '=', $conversationId, 'and')
            ->where('receiver_id', '=', $user->id, 'and')
            ->whereNull('read_at', 'and', false)
            ->update(['read_at' => $now]);

        // Clear manual unread status in pagi_unread_chats
        DB::table('pagi_unread_chats')
            ->where('user_id', $user->id)
            ->where('partner_id', $partnerId)
            ->delete();

        if ($updatedCount > 0) {
            broadcast(new PagiMessagesRead($conversationId, $user->id, $now->toISOString(), $partnerId))->toOthers();
            $myUnreadCount = PagiMessage::query()->where('receiver_id', '=', $user->id, 'and')->whereNull('read_at', 'and', false)->count('*');
            broadcast(new PagiUnreadCountUpdated($user->id, $myUnreadCount));
        }

        return [
            'success' => true,
            'read_at' => $now->toISOString(),
        ];
    }

    /**
     * Update user public key for E2EE.
     */
    public function updatePublicKey(User $user, array $publicKey): void
    {
        $metadata = $user->metadata ?? [];
        $metadata['pagi_e2e_pubkey'] = $publicKey;
        unset($metadata['pagi_e2e_privkey']);

        $user->fill(['metadata' => $metadata])->save();
    }

    /**
     * React to a message.
     */
    public function reactToMessage(User $user, PagiMessage $message, string $emoji): array
    {
        if ($user->id !== $message->sender_id && $user->id !== $message->receiver_id) {
            abort(403, 'Akses Ditolak: Anda bukan bagian dari percakapan ini.');
        }

        $reactions = $message->reactions ?? [];

        if (isset($reactions[$emoji])) {
            $userIds = $reactions[$emoji];
            if (in_array($user->id, $userIds)) {
                $reactions[$emoji] = array_values(array_filter($userIds, fn ($id) => $id !== $user->id));
                if (empty($reactions[$emoji])) {
                    unset($reactions[$emoji]);
                }
            } else {
                $reactions[$emoji][] = $user->id;
            }
        } else {
            $reactions[$emoji] = [$user->id];
        }

        $message->fill(['reactions' => $reactions])->save();

        broadcast(new PagiMessageReacted($message->id, $message->conversation_id, $reactions))->toOthers();

        return [
            'success' => true,
            'reactions' => $reactions,
        ];
    }

    /**
     * Edit an existing message.
     */
    public function editMessage(User $user, PagiMessage $message, string $body): array
    {
        if ((int) $message->sender_id !== (int) $user->id) {
            abort(403, 'Unauthorized. Hanya pengirim yang dapat mengedit pesan.');
        }

        if ($message->is_deleted) {
            abort(422, 'Pesan yang sudah dihapus tidak dapat diedit.');
        }

        $minutesSinceSent = $message->created_at->diffInMinutes(now());
        if ($minutesSinceSent > 20) {
            abort(422, 'Pesan hanya dapat diedit dalam 20 menit setelah dikirim.');
        }

        $message->fill([
            'body' => $body,
            'edited_at' => now(),
        ])->save();

        broadcast(new PagiMessageEdited(
            $message->id,
            $message->conversation_id,
            $message->body,
            $message->edited_at->toISOString()
        ))->toOthers();

        return [
            'success' => true,
            'body' => $message->body,
            'edited_at' => $message->edited_at->toISOString(),
        ];
    }

    /**
     * Delete message (for everyone or for me).
     */
    public function deleteMessage(User $user, PagiMessage $message, string $deleteType): array
    {
        $userId = (int) $user->id;
        $senderId = (int) $message->sender_id;
        $receiverId = (int) $message->receiver_id;

        if ($userId !== $senderId && $userId !== $receiverId) {
            abort(403, 'Unauthorized. Anda bukan bagian dari percakapan ini.');
        }

        $messageId = $message->id;
        $conversationId = $message->conversation_id;

        if ($deleteType === 'for_everyone') {
            if ($userId !== $senderId) {
                abort(403, 'Hanya pengirim yang dapat menghapus pesan untuk semua orang.');
            }

            $message->fill([
                'body' => '',
                'is_deleted' => true,
                'edited_at' => null,
            ])->save();

            broadcast(new PagiMessageDeleted($messageId, $conversationId, $senderId, $receiverId))->toOthers();
        } else {
            $deletedFor = array_map('intval', $message->deleted_for ?? []);
            if (! in_array($userId, $deletedFor, true)) {
                $deletedFor[] = $userId;
                $message->fill(['deleted_for' => $deletedFor])->save();
            }
        }

        return [
            'success' => true,
            'message_id' => $messageId,
            'delete_type' => $deleteType,
        ];
    }

    /**
     * Clear all conversation messages for a user (delete for me).
     */
    public function clearChat(User $user, int $partnerId): void
    {
        DB::transaction(function () use ($user, $partnerId) {
            DB::table('pagi_cleared_chats')->updateOrInsert(
                ['user_id' => $user->id, 'partner_id' => $partnerId],
                ['cleared_at' => now(), 'created_at' => now(), 'updated_at' => now()]
            );

            PagiMessage::query()->conversation($user->id, $partnerId)
                ->where('receiver_id', '=', $user->id, 'and')
                ->whereNull('read_at', 'and', false)
                ->update(['read_at' => now()]);
        });
    }

    /**
     * Block a user.
     */
    public function blockUser(User $user, int $partnerId): void
    {
        DB::table('pagi_blocks')->insertOrIgnore([
            'user_id' => $user->id,
            'blocked_id' => $partnerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Unblock a user.
     */
    public function unblockUser(User $user, int $partnerId): void
    {
        DB::table('pagi_blocks')
            ->where('user_id', $user->id)
            ->where('blocked_id', $partnerId)
            ->delete();
    }

    /**
     * Fetch list of allowed following contacts to start a new chat.
     */
    public function getContacts(User $user): array
    {
        $pagiModule = Module::query()->where('code', '=', 'PAGI', 'and')->first();
        if (! $pagiModule) {
            return [];
        }

        $userIds = UserModuleRole::query()->where('module_id', '=', $pagiModule->id, 'and')
            ->where('user_id', '!=', $user->id, 'and')
            ->where('is_active', '=', true, 'and')
            ->pluck('user_id')
            ->toArray();

        $followingIds = DB::table('pagi_follows')
            ->where('follower_id', $user->id)
            ->pluck('following_id')
            ->toArray();

        $allowedIds = array_values(array_intersect($userIds, $followingIds));

        return User::query()->whereIn('id', $allowedIds, 'and', false)
            ->select(['id', 'name', 'foto_path'])
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'foto_path' => $u->foto_path,
                'avatar' => $u->foto_path
                    ? (str_starts_with($u->foto_path, 'http') ? $u->foto_path : '/storage/'.$u->foto_path)
                    : 'https://api.dicebear.com/7.x/initials/svg?seed='.urlencode($u->name).'&backgroundColor=3b82f6',
            ])->toArray();
    }

    /**
     * Pin or unpin conversation.
     */
    public function togglePin(User $user, int $partnerId): bool
    {
        $existing = DB::table('pagi_pinned_chats')
            ->where('user_id', $user->id)
            ->where('partner_id', $partnerId)
            ->first();

        if ($existing) {
            DB::table('pagi_pinned_chats')
                ->where('user_id', $user->id)
                ->where('partner_id', $partnerId)
                ->delete();

            return false;
        }

        DB::table('pagi_pinned_chats')->insertOrIgnore([
            'user_id' => $user->id,
            'partner_id' => $partnerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Archive or unarchive conversation.
     */
    public function toggleArchive(User $user, int $partnerId): bool
    {
        $existing = DB::table('pagi_archived_chats')
            ->where('user_id', $user->id)
            ->where('partner_id', $partnerId)
            ->first();

        if ($existing) {
            DB::table('pagi_archived_chats')
                ->where('user_id', $user->id)
                ->where('partner_id', $partnerId)
                ->delete();

            return false;
        }

        DB::table('pagi_archived_chats')->insertOrIgnore([
            'user_id' => $user->id,
            'partner_id' => $partnerId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Toggle read status (Mark read / Mark manual unread).
     */
    public function toggleRead(User $user, int $partnerId): string
    {
        $convId = PagiMessage::conversationId($user->id, $partnerId);

        $hasUnreadMessages = PagiMessage::query()->where('conversation_id', '=', $convId, 'and')
            ->where('receiver_id', '=', $user->id, 'and')
            ->whereNull('read_at', 'and', false)
            ->exists();

        $isManuallyUnread = DB::table('pagi_unread_chats')
            ->where('user_id', $user->id)
            ->where('partner_id', $partnerId)
            ->exists();

        if ($hasUnreadMessages || $isManuallyUnread) {
            $now = now();
            DB::transaction(function () use ($user, $partnerId, $convId, $now) {
                PagiMessage::query()->where('conversation_id', '=', $convId, 'and')
                    ->where('receiver_id', '=', $user->id, 'and')
                    ->whereNull('read_at', 'and', false)
                    ->update(['read_at' => $now]);

                DB::table('pagi_unread_chats')
                    ->where('user_id', $user->id)
                    ->where('partner_id', $partnerId)
                    ->delete();
            });

            $status = 'read';
            broadcast(new PagiMessagesRead($convId, $user->id, $now->toISOString(), $partnerId))->toOthers();
        } else {
            DB::table('pagi_unread_chats')->insertOrIgnore([
                'user_id' => $user->id,
                'partner_id' => $partnerId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $status = 'unread';
        }

        // Broadcast updated unread count for this user
        $myUnreadCount = PagiMessage::query()->where('receiver_id', '=', $user->id, 'and')->whereNull('read_at', 'and', false)->count('*');
        broadcast(new PagiUnreadCountUpdated($user->id, $myUnreadCount));

        return $status;
    }

    /**
     * Delete entire conversation list registrations and clean unread logs.
     */
    public function deleteConversation(User $user, int $partnerId): void
    {
        DB::transaction(function () use ($user, $partnerId) {
            DB::table('pagi_cleared_chats')->updateOrInsert(
                ['user_id' => $user->id, 'partner_id' => $partnerId],
                ['cleared_at' => now(), 'created_at' => now(), 'updated_at' => now()]
            );

            PagiMessage::query()->conversation($user->id, $partnerId)
                ->where('receiver_id', '=', $user->id, 'and')
                ->whereNull('read_at', 'and', false)
                ->update(['read_at' => now()]);

            DB::table('pagi_active_chats')
                ->where('user_id', $user->id)
                ->where('partner_id', $partnerId)
                ->delete();

            DB::table('pagi_pinned_chats')
                ->where('user_id', $user->id)
                ->where('partner_id', $partnerId)
                ->delete();

            DB::table('pagi_archived_chats')
                ->where('user_id', $user->id)
                ->where('partner_id', $partnerId)
                ->delete();

            DB::table('pagi_unread_chats')
                ->where('user_id', $user->id)
                ->where('partner_id', $partnerId)
                ->delete();
        });

        // Broadcast updated unread count for this user
        $myUnreadCount = PagiMessage::query()->where('receiver_id', '=', $user->id, 'and')->whereNull('read_at', 'and', false)->count('*');
        broadcast(new PagiUnreadCountUpdated($user->id, $myUnreadCount));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function applyClearedChatsFilter($query, array $clearedChats, int $userId): void
    {
        if (empty($clearedChats)) {
            return;
        }

        $query->whereNot(function ($q) use ($clearedChats, $userId) {
            foreach ($clearedChats as $partnerId => $clearedAt) {
                $convId = PagiMessage::conversationId($userId, (int) $partnerId);
                $q->orWhere(function ($sq) use ($convId, $clearedAt) {
                    $sq->where('conversation_id', '=', $convId, 'and')
                        ->where('created_at', '<=', $clearedAt, 'and');
                });
            }
        }, null, null, 'and');
    }

    private function getMessagesQuery(User $user, User $partner, ?string $cursorId, $clearedChat)
    {
        $query = PagiMessage::query()->conversation($user->id, $partner->id)
            ->with(['sender:id,name,foto_path', 'parent.sender:id,name']);

        if ($cursorId) {
            $query->where('id', '<', (int) $cursorId, 'and');
        }

        if ($clearedChat && $clearedChat->cleared_at) {
            $query->where('created_at', '>', $clearedChat->cleared_at, 'and');
        }

        return $query;
    }

    private function markMessagesAsRead(User $user, User $partner, $clearedChat): void
    {
        $now = now();
        $unreadQuery = PagiMessage::query()->where('conversation_id', '=', PagiMessage::conversationId($user->id, $partner->id), 'and')
            ->where('receiver_id', '=', $user->id, 'and')
            ->whereNull('read_at', 'and', false);

        if ($clearedChat && $clearedChat->cleared_at) {
            $unreadQuery->where('created_at', '>', $clearedChat->cleared_at);
        }

        $updatedCount = $unreadQuery->update(['read_at' => $now]);

        DB::table('pagi_unread_chats')
            ->where('user_id', $user->id)
            ->where('partner_id', $partner->id)
            ->delete();

        if ($updatedCount > 0) {
            $conversationId = PagiMessage::conversationId($user->id, $partner->id);
            broadcast(new PagiMessagesRead($conversationId, $user->id, $now->toISOString(), (int) $partner->id))->toOthers();
            $myUnreadCount = PagiMessage::query()->where('receiver_id', '=', $user->id, 'and')->whereNull('read_at', 'and', false)->count('*');
            broadcast(new PagiUnreadCountUpdated($user->id, $myUnreadCount));
        }
    }

    private function formatConversations(
        User $user,
        $conversations,
        $unreadCounts,
        $lastMessages,
        array $blockedByMeIds,
        array $blockedMeIds,
        array $pinnedChatsIds,
        array $archivedChatsIds,
        array $unreadChatsIds,
        array $activeChatsList
    ): Collection {
        return $conversations->map(function (User $partner) use (
            $user,
            $unreadCounts,
            $lastMessages,
            $blockedByMeIds,
            $blockedMeIds,
            $pinnedChatsIds,
            $archivedChatsIds,
            $unreadChatsIds,
            $activeChatsList
        ) {
            $convId = PagiMessage::conversationId($user->id, $partner->id);
            $lastMsg = $lastMessages->get($convId);
            $unread = $unreadCounts->get($convId, 0);

            $blockedByMe = in_array((int) $partner->id, $blockedByMeIds);
            $blockedMe = in_array((int) $user->id, $blockedMeIds);

            $isPinned = in_array((int) $partner->id, $pinnedChatsIds);
            $isArchived = in_array((int) $partner->id, $archivedChatsIds);
            $isManualUnread = in_array((int) $partner->id, $unreadChatsIds);

            if (! $lastMsg && ! in_array((int) $partner->id, $activeChatsList)) {
                return null;
            }

            $lastMessageIsDeleted = $lastMsg ? (bool) $lastMsg->is_deleted : false;
            $lastMessageIsDeletedForMe = false;
            if ($lastMsg && $lastMsg->deleted_for) {
                $deletedFor = array_map('intval', $lastMsg->deleted_for);
                if (in_array((int) $user->id, $deletedFor, true)) {
                    $lastMessageIsDeletedForMe = true;
                }
            }

            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'foto_path' => $partner->foto_path,
                'last_seen_at' => $partner->last_seen_at?->toISOString(),
                'metadata' => $partner->metadata,
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
    }

    private function formatMessages($messages, User $user): Collection
    {
        return $messages->map(function ($msg) use ($user) {
            $isDeletedForMe = false;
            if ($msg->deleted_for) {
                $deletedFor = array_map('intval', $msg->deleted_for);
                if (in_array((int) $user->id, $deletedFor, true)) {
                    $isDeletedForMe = true;
                }
            }

            $isDeleted = (bool) $msg->is_deleted;

            $parentBody = null;
            if ($msg->parent) {
                $parentIsDeletedForMe = false;
                if ($msg->parent->deleted_for) {
                    $parentDeletedFor = array_map('intval', $msg->parent->deleted_for);
                    if (in_array((int) $user->id, $parentDeletedFor, true)) {
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
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'body' => ($isDeleted || $isDeletedForMe) ? '' : $msg->body,
                'is_deleted' => $isDeleted,
                'is_deleted_for_me' => $isDeletedForMe,
                'edited_at' => $msg->edited_at?->toISOString(),
                'parent_id' => $msg->parent_id,
                'parent' => $msg->parent ? [
                    'id' => $msg->parent->id,
                    'body' => $parentBody,
                    'sender' => [
                        'id' => $msg->parent->sender->id,
                        'name' => $msg->parent->sender->name,
                    ],
                ] : null,
                'read_at' => $msg->read_at ? $msg->read_at->toISOString() : null,
                'created_at' => $msg->created_at->toISOString(),
                'reactions' => $msg->reactions ?? [],
                'sender' => [
                    'id' => $msg->sender->id,
                    'name' => $msg->sender->name,
                    'foto_path' => $msg->sender->foto_path,
                ],
            ];
        });
    }
}
