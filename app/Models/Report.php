<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Report extends Model
{
    use HasFactory;

    /**
     * Clave primaria UUID (no incremental, tipo string).
     */
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Campos asignables.
     */
    protected $fillable = [
        'id',
        'name',
        'workspace_id',
        'dataset_id',
        'cover_url',
        'description',
    ];

    /**
     * Relación (ajusta si tu clase Workspace se llama distinto).
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }

    /**
     * Normaliza cover_url a string o null sin espacios.
     */
    public function getCoverUrlAttribute($value): ?string
    {
        if (is_null($value)) return null;
        $t = trim((string) $value);
        return $t !== '' ? $t : null;
    }

    /**
     * URL efectiva de portada:
     * - cover_url propia, o
     * - predeterminada en storage/app/public/report-covers/default.png, o
     * - null.
     */
    public function getEffectiveCoverUrlAttribute(): ?string
    {
        if (!empty($this->cover_url)) {
            return $this->cover_url;
        }

        $defaultPath = 'report-covers/default.png';
        if (Storage::disk('public')->exists($defaultPath)) {
            return asset('storage/'.$defaultPath);
        }

        return null;
    }
}
