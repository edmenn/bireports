<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $table = 'audits';
    
    // Asegura que Eloquent incluya el offset al persistir (e.g. "+00:00"),
    // evitando que Postgres interprete timestamps sin zona en la TZ de la sesión.
    protected $dateFormat = 'Y-m-d H:i:sP';

    protected $fillable = [
        'user_id',
        'report_uuid',
        'workspace_uuid',
        'dataset_uuid',
        'action',
        'rls_role',
        'filters',
        'page_name',
        'bookmark',
        'ip',
        'user_agent',
        'referer',
        'duration_ms',
        'occurred_at',
        'success',
        'error_message',
    ];

    protected $casts = [
        'filters'     => 'array',
        'occurred_at' => 'datetime',
        'success'     => 'boolean',
    ];
}
