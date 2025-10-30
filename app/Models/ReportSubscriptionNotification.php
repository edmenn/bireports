<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportSubscriptionNotification extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_USER = 'user';
    public const TYPE_GROUP = 'group';
    public const TYPE_WORKSPACE_ADMINS = 'workspace_admins';

    protected $fillable = [
        'report_subscription_id',
        'channel',
        'notifiable_type',
        'notifiable_id',
    ];

    protected $casts = [
        'notifiable_id' => 'integer',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ReportSubscription::class, 'report_subscription_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'notifiable_id');
    }
}
