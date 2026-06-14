<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Response extends Model
{
    use HasFactory;

    protected $table = 'responses';

    protected $fillable = [
        'user_id',
        'kuesioner_id',
        'submitted_at',
        'angkatan',
    ];

    /*
    |-------------------------
    | CASTS
    |-------------------------
    */
    protected $casts = [
        'submitted_at' => 'datetime',
        'angkatan' => 'integer',
    ];

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */

    // Response dimiliki user (alumni)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Response untuk 1 kuesioner
    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class);
    }

    // Response punya banyak detail jawaban
    public function detailJawabans()
    {
        return $this->hasMany(DetailJawaban::class);
    }
}