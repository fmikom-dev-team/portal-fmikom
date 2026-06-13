<?php

namespace App\Models\Magang;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPenetapan extends Model
{
    protected $table = 'surat_penetapans';

    protected $fillable = [
        'pendaftaran_id',
        'requested_by',
        'status',
        'provider',
        'fast_reference_id',
        'nomor_surat',
        'file_url',
        'requested_at',
        'generated_at',
        'error_message',
        'payload_snapshot',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'generated_at' => 'datetime',
        'payload_snapshot' => 'array',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
