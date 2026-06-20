<?php

namespace App\Models\Traits;

use App\Models\Pagi\PagiActiveChat;
use App\Models\Pagi\PagiArchivedChat;
use App\Models\Pagi\PagiClearedChat;
use App\Models\Pagi\PagiPinnedChat;
use App\Models\Pagi\PagiUnreadChat;
use App\Models\Pagi\PagiWork;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPagiRelations
{
    public function pagiFollowers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pagi_follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function pagiFollowing(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pagi_follows', 'follower_id', 'following_id')->withTimestamps();
    }

    public function pagiBlocks(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pagi_blocks', 'user_id', 'blocked_id')->withTimestamps();
    }

    public function pagiPinnedChats(): HasMany
    {
        return $this->hasMany(PagiPinnedChat::class, 'user_id');
    }

    public function pagiArchivedChats(): HasMany
    {
        return $this->hasMany(PagiArchivedChat::class, 'user_id');
    }

    public function pagiActiveChats(): HasMany
    {
        return $this->hasMany(PagiActiveChat::class, 'user_id');
    }

    public function pagiUnreadChats(): HasMany
    {
        return $this->hasMany(PagiUnreadChat::class, 'user_id');
    }

    public function pagiClearedChats(): HasMany
    {
        return $this->hasMany(PagiClearedChat::class, 'user_id');
    }

    public function pagiWorks(): HasMany
    {
        return $this->hasMany(PagiWork::class, 'user_id');
    }
}
