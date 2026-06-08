<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Kuesioner;
use App\Models\Section;
use App\Models\OpsiJawaban;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pertanyaan extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pertanyaan';

    protected $fillable = [
        'kuesioner_id',
        'section_id',
        'indikator_id',
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
        'is_required'    => 'boolean',
        'meta'           => 'array',
        'acuan'          => 'array',
        'logic_condition' => 'array',
        'skoring'        => 'array',
    ];

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */

     public function indikator()
    {
        return $this->belongsTo(IndikatorLam::class, 'indikator_id');
    }
    
    // Pertanyaan milik 1 kuesioner
    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class);
    }

    // Pertanyaan milik 1 section
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // Pertanyaan punya banyak opsi jawaban
    public function opsiJawabans()
    {
        return $this->hasMany(OpsiJawaban::class);
    }
}