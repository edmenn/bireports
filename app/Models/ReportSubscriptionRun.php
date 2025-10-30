<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportSubscriptionRun extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'report_subscription_id',
        'scheduled_time',
        'start_time',
        'finish_time',
        'status',
        'error_message',
        'recipients_total',
        'recipients_sent',
        'recipients_failed',
    ];

    protected $casts = [
        'scheduled_time'    => 'datetime',
        'start_time'        => 'datetime',
        'finish_time'       => 'datetime',
        'recipients_total'  => 'integer',
        'recipients_sent'   => 'integer',
        'recipients_failed' => 'integer',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ReportSubscription::class, 'report_subscription_id');
    }
}

