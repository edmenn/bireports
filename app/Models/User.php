<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
     * === Métodos esperados por AdminLTE ===
     */

    // IMPORTANTE: con 'use_route_url' => true, debe devolver NOMBRE de ruta
    public function adminlte_profile_url(): string
    {
        return 'profile.edit';
    }

    public function adminlte_image(): ?string
    {
        return null;
    }

    public function adminlte_desc(): ?string
    {
        return null;
    }

    /**
     * Relación N:M con Company (del mismo cliente)
     * (si aún no la usás, no molesta)
     */
    public function companies()
    {
        return $this->belongsToMany(\App\Models\Company::class);
    }
}
