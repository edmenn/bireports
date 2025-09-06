<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityAudit extends Model
{
    protected $table = 'entity_audits';

    protected $fillable = [
        'user_id', 'action', 'auditable_type', 'auditable_id', 'changes', 'meta',
    ];

    protected $casts = [
        'changes' => 'array',
        'meta'    => 'array',
    ];
}

