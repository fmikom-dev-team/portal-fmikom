<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisSurat extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'nama',
        'slug',
        'kode_surat',
        'kode_klasifikasi',
        'deskripsi',
        'field_config',
        'template_file_path',
        'template_surat',
        'allowed_role_id',
        'approval_role_id',
        'perlu_approval',
        'alur_pengajuan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'field_config' => 'array',
            'perlu_approval' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Surat, $this>
     */
    public function surats(): HasMany
    {
        return $this->hasMany(Surat::class);
    }

    /**
     * @return HasOne<SuratTemplate, $this>
     */
    public function template(): HasOne
    {
        return $this->hasOne(SuratTemplate::class)->where('is_active', true)->latestOfMany();
    }

    /**
     * @return BelongsTo<SuratCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SuratCategory::class, 'category_id');
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function allowedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'allowed_role_id');
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function approvalRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approval_role_id');
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'jenis_surat_roles');
    }
}
