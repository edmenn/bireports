<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConfigurationThemePreset extends Model
{
    use HasFactory;

    protected $table = 'configuration_theme_presets';

    protected $fillable = [
        'configuration_id',
        'name',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function configuration()
    {
        return $this->belongsTo(Configuration::class);
    }
}
