<?php

namespace App\Models\Surat;

use App\Models\JenisSurat;
use App\Models\Magang\PendaftaranMagang;
use App\Models\SuratLampiran;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Surat extends Model
{
    protected $fillable = [
        'jenis_surat_id', 'pemohon_id', 'nomor_surat', 'keperluan',
        'status', 'isi_surat', 'tanggal_pengajuan', 'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function approvalFlows(): HasMany
    {
        return $this->hasMany(SuratApprovalFlow::class);
    }

    public function lampirans(): HasMany
    {
        return $this->hasMany(SuratLampiran::class);
    }

    public function pendaftaranMagang(): HasOne
    {
        return $this->hasOne(PendaftaranMagang::class, 'surat_tugas_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function getNextApprover(): ?User
    {
        $flow = $this->approvalFlows()->where('status', 'pending')->orderBy('urutan')->first();

        return $flow ? $flow->approver : null;
    }

    public function isApprovable(): bool
    {
        return $this->status === 'pending';
    }
}
