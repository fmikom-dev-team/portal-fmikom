<?php

namespace App\Models\Pagi;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagiCustomWork extends Model
{
    use HasFactory;

    protected $table = 'pagi_custom_works';

    protected $fillable = [
        'user_id',
        'theme',
        'palette_index',
        'is_published',
        'custom_title',
        'custom_bio',
        'selected_projects',
        'logo_path',
    ];

    protected $casts = [
        'selected_projects' => 'array',
        'is_published' => 'boolean',
        'palette_index' => 'integer',
    ];

    /**
     * Get the user that owns the custom layout / work page.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
