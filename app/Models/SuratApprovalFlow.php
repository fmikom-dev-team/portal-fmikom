<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratApprovalFlow extends Model
{
    public const STATUS_APPROVED = 'approved';

    public const STATUS_REVISION_REQUESTED = 'revision_requested';

    public const STATUS_REJECTED_FINAL = 'rejected_final';

    public const STATUS_REJECTED = self::STATUS_REJECTED_FINAL;

    public const STATUS_NOTE = 'note';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_KAPRODI = 'kaprodi';

    public const ROLE_DEKAN = 'dekan';

    protected $fillable = [
        'surat_id',
        'approver_id',
        'urutan',
        'role',
        'status',
        'keterangan',
        'approved_at',
        'catatan',
        'tanggal_aksi',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'tanggal_aksi' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Surat, $this>
     */
    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
