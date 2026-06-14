<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
class Kuesioner extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'kuesioner';

    protected $fillable = [
        'judul',
        'subtitle',
        'kategori',
        'tahun',
        'date_mulai',
        'date_selesai',
        'deskripsi',
        'is_active',
        'status',
        'tipe_kuesioner',
        'created_by',
    ];

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function responses()
{
    return $this->hasMany(Response::class, 'kuesioner_id');
}

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}