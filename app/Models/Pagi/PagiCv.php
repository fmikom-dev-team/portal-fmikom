<?php

namespace App\Models\Pagi;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method bool|null delete()
 */
class PagiCv extends Model
{
    use HasFactory;

    protected $table = 'pagi_cvs';

    protected $fillable = [
        'user_id',
        'title',
        'template_id',
        'personal_info',
        'education',
        'experience',
        'organizations',
        'skills',
        'certifications',
        'trainings',
        'achievements',
        'languages',
        'references',
        'customization',
        'status',
    ];

    protected $casts = [
        'personal_info' => 'array',
        'education' => 'array',
        'experience' => 'array',
        'organizations' => 'array',
        'skills' => 'array',
        'certifications' => 'array',
        'trainings' => 'array',
        'achievements' => 'array',
        'languages' => 'array',
        'references' => 'array',
        'customization' => 'array',
    ];

    /**
     * Get the user that owns the CV.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
