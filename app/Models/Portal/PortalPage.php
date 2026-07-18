<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class PortalPage extends Model
{
    use Searchable;

    protected $guarded = [];

    /**
     * Index lean fields.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category ?? '',
        ];
    }
}
