<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'name',
        'slug',
        'parent_id',
        'display_order',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'application_report', 'section_id', 'report_id')
            ->withPivot(['application_id', 'display_order'])
            ->withTimestamps();
    }
}