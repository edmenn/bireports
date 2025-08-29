<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CapacitySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'capacity_id',
        'weekday',       // 0=Dom ... 6=Sab
        'start_time',    // HH:MM:SS (TIME)
        'end_time',      // HH:MM:SS (TIME)
        'enabled',
    ];

    protected $casts = [
        'weekday' => 'integer',
        'enabled' => 'boolean',
    ];

    public function capacity()
    {
        return $this->belongsTo(Capacity::class);
    }

    /**
     * Accessor para devolver un Carbon "de hoy" con la hora de start_time.
     * Así los Jobs pueden comparar directamente con now().
     */
    public function getStartTimeAttribute($value): Carbon
    {
        // $value = 'HH:MM:SS' (cadena)
        [$h, $m, $s] = array_map('intval', explode(':', (string) $value) + [0, 0, 0]);
        return Carbon::today()->setTime($h, $m, $s);
    }

    /**
     * Accessor para devolver un Carbon "de hoy" con la hora de end_time.
     */
    public function getEndTimeAttribute($value): Carbon
    {
        [$h, $m, $s] = array_map('intval', explode(':', (string) $value) + [0, 0, 0]);
        return Carbon::today()->setTime($h, $m, $s);
    }
}
