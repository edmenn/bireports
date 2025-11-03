<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportSubscription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_enabled',
        'format',
        'recurrence',
        'weekly_days',
        'weekly_interval',
        'monthly_day',
        'monthly_use_last_day',
        'cron_expression',
        'start_date',
        'end_date',
        'hour',
        'minute',
        'timezone',
        'report_id',
        'mail_subject',
        'mail_body',
        'max_attachment_size_mb',
        'notify_workspace_admins',
        'ensure_capacity_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_enabled'              => 'boolean',
        'start_date'              => 'date',
        'end_date'                => 'date',
        'mail_body'               => 'string',
        'notify_workspace_admins' => 'boolean',
        'ensure_capacity_active'  => 'boolean',
        'monthly_use_last_day'    => 'boolean',
    ];

    public const RECURRENCE_ONCE    = 'once';
    public const FORMAT_PDF = 'pdf';
    public const FORMAT_PPT = 'ppt';

    public const RECURRENCE_DAILY   = 'daily';
    public const RECURRENCE_WEEKLY  = 'weekly';
    public const RECURRENCE_MONTHLY = 'monthly';
    public const RECURRENCE_CRON    = 'cron';

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'report_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(ReportSubscriptionPage::class);
    }

    public function groupLinks(): HasMany
    {
        return $this->hasMany(ReportSubscriptionGroup::class);
    }

    public function userLinks(): HasMany
    {
        return $this->hasMany(ReportSubscriptionUser::class);
    }

    public function notificationLinks(): HasMany
    {
        return $this->hasMany(ReportSubscriptionNotification::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ReportSubscriptionRun::class)->latest('scheduled_time');
    }

    public function recipientGroups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'report_subscription_groups')
            ->withPivot(['id', 'deleted_at'])
            ->wherePivotNull('deleted_at');
    }

    public function recipientUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'report_subscription_users')
            ->withPivot(['id', 'deleted_at'])
            ->wherePivotNull('deleted_at');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function inEffectAt(CarbonInterface $moment): bool
    {
        $start = $this->start_date;
        $end   = $this->end_date;

        if ($start && $moment->lessThan($start->startOfDay())) {
            return false;
        }

        if ($end && $moment->greaterThan($end->endOfDay())) {
            return false;
        }

        return true;
    }
}
