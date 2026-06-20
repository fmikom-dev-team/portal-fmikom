<?php

namespace App\Models\Pagi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagiPinnedChat extends Model
{
    protected $table = 'pagi_pinned_chats';

    protected $fillable = [
        'user_id',
        'partner_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}
