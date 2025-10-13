<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Report extends Model
{
    use HasFactory;

    /**
     * UUID primary key (string, non-incrementing).
     */
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'id',
        'name',
        'workspace_id',
        'dataset_id',
        'cover_url',
        'description',
        'allow_resume_outside_window',
    ];

    protected $casts = [
        'allow_resume_outside_window' => 'boolean',
    ];

    /**
     * Workspace relationship (adjust if your Workspace model uses a different class).
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }

    /**
     * Associations with portal applications.
     */
    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class, 'application_report')
            ->withPivot(['section_id', 'display_order'])
            ->withTimestamps();
    }

    /**
     * Normalize cover_url to a trimmed string or null.
     */
    public function getCoverUrlAttribute($value): ?string
    {
        if (is_null($value)) return null;
        $t = trim((string) $value);
        return $t !== '' ? $t : null;
    }

    /**
     * Resolve the effective cover URL:
     * - explicit cover_url
     * - default stored at storage/app/public/report-covers/default.png
     * - null otherwise
     */
    public function getEffectiveCoverUrlAttribute(): ?string
    {
        if (!empty($this->cover_url)) {
            return $this->cover_url;
        }

        $defaultPath = 'report-covers/default.png';
        if (Storage::disk('public')->exists($defaultPath)) {
            return asset('storage/'.$defaultPath);
        }

        return null;
    }
}
