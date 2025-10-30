<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ReportSubscription;
use App\Models\ReportSubscriptionRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

class SubscriptionFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ReportSubscription $subscription,
        private readonly ReportSubscriptionRun $run,
        private readonly string $message
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if (Schema::hasTable('notifications')) {
            $channels[] = 'database';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('subscriptions.notifications.failure.subject', [
                'name' => $this->subscription->name,
            ]))
            ->line(__('subscriptions.notifications.failure.intro', [
                'name' => $this->subscription->name,
            ]))
            ->line($this->message)
            ->action(
                __('subscriptions.notifications.failure.action'),
                route('subscriptions.show', $this->subscription->id)
            )
            ->line(__('subscriptions.notifications.failure.footer'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'run_id' => $this->run->id,
            'message' => $this->message,
            'status' => $this->run->status,
        ];
    }
}
