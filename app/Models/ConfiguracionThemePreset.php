<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConfiguracionThemePreset extends Model
{
    use HasFactory;

    protected $table = 'configuracion_theme_presets';

    protected $fillable = [
        'configuracion_id',
        'name',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function configuracion()
    {
        return $this->belongsTo(Configuracion::class);
    }
}
