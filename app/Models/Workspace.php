<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    /**
     * Primary key is a Power BI provided UUID (string).
     */
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'id',
        'name',
        'type',
        'is_read_only',
        'capacity_id',
    ];

    /**
     * Attribute casts.
     */
    protected $casts = [
        'is_read_only' => 'boolean',
    ];

    /**
     * Relationships.
     */
    public function reports(): HasMany
    {
        // FK: reports.workspace_id -> workspaces.id
        return $this->hasMany(Report::class, 'workspace_id', 'id');
    }

    public function datasets(): HasMany
    {
        return $this->hasMany(Dataset::class, 'workspace_id', 'id');
    }
}
