<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapacityAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'capacity_id',
        'action',
        'result',
        'message',
    ];
}

