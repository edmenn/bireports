<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    /**
     * Tabla y PK
     */
    protected $table = 'workspaces';
    protected $primaryKey = 'id';
    public $incrementing = false;     // el id viene de Power BI (string/uuid)
    protected $keyType = 'string';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'id',
        'name',
        'capacity_id',
        'is_on_premium_capacity',
        // si tu migración tiene más columnas, agrégalas aquí
    ];

    /**
     * Casts
     */
    protected $casts = [
        'is_on_premium_capacity' => 'boolean',
    ];

    /**
     * Relaciones
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'workspace_id', 'id');
    }
}
