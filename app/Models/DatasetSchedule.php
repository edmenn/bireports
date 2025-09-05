<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'dataset_id',
        'days',
        'times',
        'enabled',
        'last_run_at',
    ];

    protected $casts = [
        'days'        => 'array',
        'times'       => 'array',
        'enabled'     => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class, 'dataset_id', 'id');
    }
}

