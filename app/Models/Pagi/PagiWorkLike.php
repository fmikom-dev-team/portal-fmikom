<?php

namespace App\Models\Pagi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PagiWorkLike extends Model
{
    protected $table = 'pagi_work_likes';

    protected $fillable = [
        'work_id',
        'user_id',
    ];

    public function work()
    {
        return $this->belongsTo(PagiWork::class, 'work_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
