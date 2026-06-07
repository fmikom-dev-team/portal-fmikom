<?php

namespace App\Models\Surat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratApprovalFlow extends Model
{
    protected $fillable = [
        'surat_id', 'approver_id', 'urutan', 'status', 'catatan', 'tanggal_aksi'
    ];

    protected $casts = [
        'tanggal_aksi' => 'datetime'
    ];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approve(): void
    {
        $this->update(['status' => 'approved', 'tanggal_aksi' => now()]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected', 'tanggal_aksi' => now()]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}