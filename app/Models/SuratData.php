<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratData extends Model
{
    protected $table = 'surat_data';

    protected $fillable = [
        'surat_id',
        'field_name',
        'field_value',
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }
}
