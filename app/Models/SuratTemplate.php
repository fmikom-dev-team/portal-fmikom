<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'jenis_surat_id',
        'name',
        'slug',
        'format',
        'source_reference',
        'subject',
        'template_header',
        'template_body',
        'template_footer',
        'version',
        'is_active',
        'css_style',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<JenisSurat, $this>
     */
    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }

    /**
     * @return HasMany<SuratTemplatePlaceholder, $this>
     */
    public function placeholders(): HasMany
    {
        return $this->hasMany(SuratTemplatePlaceholder::class)->orderBy('id');
    }
}
