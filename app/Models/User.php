<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Guard por defecto para Spatie (web).
     */
    protected string $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'theme',
        'auth_provider',   // ?? nuevo
        'external_id',     // ?? nuevo
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * === Metodos esperados por AdminLTE ===
     */

    public function adminlte_profile_url(): string
    {
        // Devolver nombre de ruta si AdminLTE usa route();
        // caso contrario, devolver el slug para url().
        if (config('adminlte.use_route_url', false)) {
            return 'profile.edit'; // nombre de la ruta
        }
        return 'profile'; // slug relativo (url('profile') => /profile)
    }

    public function adminlte_image(): ?string
    {
        return null;
    }

    public function adminlte_desc(): ?string
    {
        return null;
    }

    public function groups()
    {
        return $this->belongsToMany(\App\Models\Group::class);
    }

    public function folders()
    {
        return $this->belongsToMany(\App\Models\Folder::class);
    }

    public function applications()
    {
        return $this->belongsToMany(\App\Models\Application::class, 'application_user')->withTimestamps();
    }

    public function favoriteReports()
    {
        return $this->belongsToMany(\App\Models\Report::class, 'user_favorites', 'user_id', 'report_id');
    }

    /**
     * Helpers de proveedor
     */
    public function isExternal(): bool
    {
        return !empty($this->auth_provider) && $this->auth_provider !== 'local';
    }

    public function isEntra(): bool
    {
        return $this->auth_provider === 'entra';
    }
}