<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratTemplatePlaceholder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'surat_template_id',
        'placeholder_key',
        'label',
        'source_type',
        'source_key',
        'is_required',
        'default_value',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<SuratTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(SuratTemplate::class, 'surat_template_id');
    }
}
