<?php

namespace App\Models\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthEmailLog extends Model
{
    use HasFactory;

    protected $table = 'auth_email_logs';

    protected $fillable = [
        'user_id',
        'email',
        'subject',
        'body',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
