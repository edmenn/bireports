<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    /**
     * La PK es UUID (string) provista por Power BI.
     */
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Columnas asignables (coinciden con tu migración de reports).
     */
    protected $fillable = [
        'id',
        'name',
        'workspace_id',
        'dataset_id',
        'web_url',
    ];

    /**
     * Relaciones
     */
    public function workspace(): BelongsTo
    {
        // FK: reports.workspace_id -> workspaces.id
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }
}
