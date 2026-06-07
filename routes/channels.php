<?php

use App\Models\Pagi\PagiMessage;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Authorize the radar.alerts channel for Super Admins
Broadcast::channel('radar.alerts', function ($user) {
    // Basic check. Modify according to your Spatie permission or logic
    return $user->user_type === 'super_admin' || $user->hasRole('super-admin');
});

// ── PAGI Chat private channels ────────────────────────────────────────────────
// Channel name format: pagi.chat.{smallerId}_{largerId}
// A user may only subscribe if their ID is one of the two in the conversation ID.
Broadcast::channel('pagi.chat.{conversationId}', function ($user, string $conversationId) {
    [$idA, $idB] = array_map('intval', explode('_', $conversationId, 2));
    return in_array((int) $user->id, [$idA, $idB], true);
});

Broadcast::channel('pagi.online', function ($user) {
    return [
        'id'   => $user->id,
        'name' => $user->name,
    ];
});

Broadcast::channel('pagi.admin.reports', function ($user) {
    if ($user->user_type === 'super-admin' || $user->user_type === 'admin' || $user->user_type === 'super_admin') {
        return true;
    }
    return \App\Models\UserModuleRole::where('user_id', $user->id)
        ->where('is_active', true)
        ->whereHas('module', fn($q) => $q->where('code', 'PAGI')->where('is_active', true))
        ->whereHas('role', fn($q) => $q->whereIn('slug', ['super-admin', 'admin']))
        ->exists();
});

