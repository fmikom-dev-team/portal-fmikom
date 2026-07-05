<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pertanyaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pertanyaan';

    protected $fillable = [
        'kuesioner_id',
        'section_id',
        'teks',
        'tipe',
        'tipe_data',
        'is_required',
        'urutan',
        'meta',
        'kategori',
        'acuan',
        'logic_condition',
        'skoring',
    ];

    /*
    |-------------------------
    | CASTS
    |-------------------------
    */
    protected $casts = [
        'is_required' => 'boolean',
        'meta' => 'array',
        'acuan' => 'array',
        'logic_condition' => 'array',
        'skoring' => 'array',
    ];

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */

    /**
     * Pertanyaan milik 1 kuesioner
     *
     * @return BelongsTo<Kuesioner, $this>
     */
    public function kuesioner(): BelongsTo
    {
        return $this->belongsTo(Kuesioner::class);
    }

    /**
     * Pertanyaan milik 1 section
     *
     * @return BelongsTo<Section, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Pertanyaan punya banyak opsi jawaban
     *
     * @return HasMany<OpsiJawaban, $this>
     */
    public function opsiJawabans(): HasMany
    {
        return $this->hasMany(OpsiJawaban::class);
    }
}
