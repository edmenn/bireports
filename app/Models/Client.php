<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'custom_domain',
        'ga_tracking_code',
        'default_view_type',
        'default_email_lang',
        'branding',
        'login',
        'access_schedule',
        'powerbi_client_id',
        'powerbi_tenant_id',
        'powerbi_client_secret_hint',
    ];

    protected $casts = [
        'branding'        => 'array',
        'login'           => 'array',
        'access_schedule' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
