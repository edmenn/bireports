<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];
}

