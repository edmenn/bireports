<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    /**
     * Tabla y PK
     */
    protected $table = 'reports';
    protected $primaryKey = 'id';
    public $incrementing = false;   // el id viene de Power BI (string/uuid)
    protected $keyType = 'string';

    /**
     * Campos asignables
     *
     * Deben coincidir con las columnas de tu migración de reports:
     * id (string, PK), name, workspace_id (string, FK), dataset_id (string, nullable),
     * web_url (string, nullable), embed_url (string, nullable), is_rdl (bool, default false)
     */
    protected $fillable = [
        'id',
        'name',
        'workspace_id',
        'dataset_id',
        'web_url',
        'embed_url',
        'is_rdl',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'is_rdl' => 'boolean',
    ];

    /**
     * Relaciones
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }
}
