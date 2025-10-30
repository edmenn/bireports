<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportSubscriptionGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'report_subscription_id',
        'group_id',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(ReportSubscription::class, 'report_subscription_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}

