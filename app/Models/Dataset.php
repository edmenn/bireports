<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dataset extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'workspace_id',
        'configured_by',
        'is_refreshable',
        'add_rows_api_enabled',
    ];

    protected $casts = [
        'is_refreshable'      => 'boolean',
        'add_rows_api_enabled'=> 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(DatasetSchedule::class, 'dataset_id', 'id');
    }
}
