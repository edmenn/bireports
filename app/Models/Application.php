<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $application) {
            if (empty($application->uuid)) {
                $application->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ApplicationSection::class)->orderBy('name');
    }

    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'application_report')
            ->withPivot(['section_id', 'display_order'])
            ->withTimestamps()
            ->orderBy('reports.name');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'application_user')->withTimestamps();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'application_group')->withTimestamps();
    }

    public function scopeAccessibleFor(Builder $query, User $user): Builder
    {
        $userId = (int) $user->id;
        $groupIds = $user->groups()->pluck('groups.id')->all();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($userId, $groupIds) {
                $q->whereHas('users', function ($w) use ($userId) {
                    $w->where('users.id', $userId);
                });

                if (! empty($groupIds)) {
                    $q->orWhereHas('groups', function ($w) use ($groupIds) {
                        $w->whereIn('groups.id', $groupIds);
                    });
                }
            });
    }
}
