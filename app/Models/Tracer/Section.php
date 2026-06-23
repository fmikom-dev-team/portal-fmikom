<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';

    protected $fillable = [
        'kuesioner_id',
        'title',
        'description',
        'order',
        'conditions',
        
    ];

    /*
    |-------------------------
    | CASTS
    |-------------------------
    */
    protected $casts = [
        'conditions' => 'array',
    ];

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */

    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class);
    }

    public function pertanyaans()
    {
        return $this->hasMany(Pertanyaan::class);
    }
}