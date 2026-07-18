<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class PortalDocument extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'description',
        'category',
        'file_path',
        'file_type',
        'file_size',
        'download_count',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'download_count' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'category' => $this->category ?? '',
            'file_type' => $this->file_type ?? '',
        ];
    }
}
