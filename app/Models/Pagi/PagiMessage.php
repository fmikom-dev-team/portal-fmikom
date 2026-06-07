<?php

namespace App\Models\Pagi;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagiMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'parent_id',
        'body',
        'read_at',
        'reactions',
        'deleted_for',
        'edited_at',
        'is_deleted',
    ];

    protected $casts = [
        'sender_id'   => 'integer',
        'receiver_id' => 'integer',
        'parent_id'   => 'integer',
        'read_at'     => 'datetime',
        'edited_at'   => 'datetime',
        'is_deleted'  => 'boolean',
        'reactions'   => 'array',
        'deleted_for' => 'array',
    ];

    /**
     * Scope: Hanya tampilkan pesan yang tidak dihapus oleh user tertentu.
     * Digunakan untuk fitur "Hapus hanya di saya" (WhatsApp-style).
     */
    public function scopeVisibleTo($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('deleted_for')
              ->orWhereRaw('NOT JSON_CONTAINS(deleted_for, CAST(? AS JSON), \'$\')', [$userId]);
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Generate a canonical conversation ID for two users.
     * Always sorted so conversation_id is consistent regardless of who initiates.
     */
    public static function conversationId(int $userA, int $userB): string
    {
        $ids = [$userA, $userB];
        sort($ids);
        return implode('_', $ids);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope to fetch all messages in a conversation between two users.
     */
    public function scopeConversation($query, int $userA, int $userB)
    {
        return $query->where('conversation_id', self::conversationId($userA, $userB));
    }
}
