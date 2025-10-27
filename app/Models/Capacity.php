<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capacity extends Model
{
    use HasFactory;

    protected $fillable = [
        'capacity_uuid',
        'display_name',
        'arm_resource_name',           // Newly added field placeholder
        'sku',
        'type',
        'region',
        'supports_pause',
        'is_active',
        'cost_optimization_enabled',
        'inactivity_timeout_minutes',
        'force_shutdown_on_schedule',
        'last_state_change_at',
        'outside_window_grace_until',
    ];

    protected $casts = [
        'supports_pause'             => 'boolean',
        'is_active'                  => 'boolean',
        'cost_optimization_enabled'  => 'boolean',
        'force_shutdown_on_schedule' => 'boolean',
        'inactivity_timeout_minutes' => 'integer',
        'last_state_change_at'       => 'datetime',
        'outside_window_grace_until' => 'datetime',
        'created_at'                 => 'datetime',
        'updated_at'                 => 'datetime',
    ];

    /** Relationships */
    public function schedules()
    {
        return $this->hasMany(CapacitySchedule::class);
    }

    public function activity()
    {
        return $this->hasOne(CapacityActivity::class);
    }

    /** Helper methods */
    public function isPausable(): bool
    {
        // General rule: pause must be supported and type must not be PPU
        return $this->supports_pause === true && strtoupper((string) $this->type) !== 'PPU';
    }
}
