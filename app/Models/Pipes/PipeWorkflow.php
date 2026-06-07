<?php

namespace App\Models\Pipes;

use App\Models\Module;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipeWorkflow extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'config' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Module::class, 'organization_id');
    }

    public function triggers()
    {
        return $this->hasMany(PipeTrigger::class, 'workflow_id');
    }

    public function actions()
    {
        return $this->hasMany(PipeAction::class, 'workflow_id')->orderBy('step_order');
    }
}
