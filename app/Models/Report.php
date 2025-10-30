<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'cover_scale',
        'cover_scale_x',
        'cover_scale_y',
        'description',
        'allow_resume_outside_window',
    ];

    protected $casts = [
        'allow_resume_outside_window' => 'boolean',
        'cover_scale' => 'float',
        'cover_scale_x' => 'float',
        'cover_scale_y' => 'float',
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

    public function folders(): BelongsToMany
    {
        return $this->belongsToMany(Folder::class, 'folder_report', 'report_id', 'folder_id')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ReportSubscription::class, 'report_id', 'id');
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

    /**
     * Clamp the persisted cover scale to the supported range.
     */
    public function getCoverScaleAttribute($value): float
    {
        if (!is_numeric($value)) {
            $value = $this->attributes['cover_scale_x'] ?? $this->attributes['cover_scale_y'] ?? 1.0;
        }
        return $this->normalizeScaleValue($value);
    }

    /**
     * Persist cover scale keeping range constraints.
     */
    public function setCoverScaleAttribute($value): void
    {
        $this->attributes['cover_scale'] = $this->normalizeScaleValue($value);
    }

    public function getCoverScaleXAttribute($value): float
    {
        if (!is_numeric($value)) {
            $value = $this->attributes['cover_scale'] ?? $this->attributes['cover_scale_y'] ?? 1.0;
        }
        return $this->normalizeScaleValue($value);
    }

    public function setCoverScaleXAttribute($value): void
    {
        $this->attributes['cover_scale_x'] = $this->normalizeScaleValue($value);
    }

    public function getCoverScaleYAttribute($value): float
    {
        if (!is_numeric($value)) {
            $value = $this->attributes['cover_scale'] ?? $this->attributes['cover_scale_x'] ?? 1.0;
        }
        return $this->normalizeScaleValue($value);
    }

    public function setCoverScaleYAttribute($value): void
    {
        $this->attributes['cover_scale_y'] = $this->normalizeScaleValue($value);
    }

    protected function normalizeScaleValue($value): float
    {
        $numeric = is_numeric($value) ? (float) $value : 1.0;
        return min(1.5, max(0.5, $numeric));
    }
}
