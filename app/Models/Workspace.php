<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    /**
     * La PK es UUID (string) provista por Power BI.
     */
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Columnas asignables (coinciden con tu migración de workspaces).
     */
    protected $fillable = [
        'id',
        'name',
        'type',
        'is_read_only',
        'capacity_id',
    ];

    /**
     * Casts de atributos.
     */
    protected $casts = [
        'is_read_only' => 'boolean',
    ];

    /**
     * Relaciones
     */
    public function reports(): HasMany
    {
        // FK: reports.workspace_id -> workspaces.id
        return $this->hasMany(Report::class, 'workspace_id', 'id');
    }
}
