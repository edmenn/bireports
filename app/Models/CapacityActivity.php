<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapacityActivity extends Model
{
    use HasFactory;

    protected $table = 'capacity_activity';

    protected $fillable = [
        'capacity_id',
        'last_seen_at',
        'hits_last_window',
    ];

    protected $casts = [
        'last_seen_at'     => 'datetime',
        'hits_last_window' => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function capacity()
    {
        return $this->belongsTo(Capacity::class);
    }
}
