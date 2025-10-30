<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ReportSubscription;
use App\Models\ReportSubscriptionRun;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SubscriptionDeliveryMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly ReportSubscription $subscription,
        public readonly ReportSubscriptionRun $run,
        public readonly User $recipient,
        public readonly string $subjectLine,
        public readonly string $bodyHtml,
        public readonly string $attachmentPath
    ) {
    }

    public function build(): self
    {
        $extension = pathinfo($this->attachmentPath, PATHINFO_EXTENSION);
        $filename = Str::slug($this->subscription->name ?: 'report-subscription') . '.' . $extension;

        return $this->subject($this->subjectLine)
            ->html($this->bodyHtml)
            ->attach($this->attachmentPath, [
                'as' => $filename,
            ]);
    }
}

