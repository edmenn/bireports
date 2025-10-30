<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function applications()
    {
        return $this->belongsToMany(Application::class, 'application_group')->withTimestamps();
    }

    public function folders()
    {
        return $this->belongsToMany(Folder::class, 'folder_group')->withTimestamps();
    }
}
