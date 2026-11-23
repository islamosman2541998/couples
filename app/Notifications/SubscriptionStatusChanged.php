<?php

namespace App\Notifications;

use App\Models\Setting;
use App\Models\Subscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionStatusChanged extends Notification
{
    public function __construct(public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subscription = $this->subscription;
        $product = $subscription->product_name;
        $site = Setting::get('site_name', config('app.name'));

        if ($subscription->status === 'approved') {
            $url = $subscription->is_bundle || ! $subscription->game
                ? route('home').'#games'
                : route('games.play', $subscription->game->slug);

            return (new MailMessage)
                ->subject("اشتراكك في {$product} اتفعّل")
                ->greeting("أهلاً {$subscription->full_name}")
                ->line("راجعنا الإيصال، واشتراكك في «{$product}» بقى مفعّل.")
                ->line('ادخلوا بنفس الحساب وابدأوا اللعب في أي وقت.')
                ->action('ابدأوا اللعب', $url)
                ->salutation("سهرة حلوة — {$site}");
        }

        $message = (new MailMessage)
            ->subject("بخصوص طلب اشتراكك في {$product}")
            ->greeting("أهلاً {$subscription->full_name}")
            ->line("للأسف ماقدرناش نفعّل طلب اشتراكك في «{$product}».");

        if (filled($subscription->admin_notes)) {
            $message->line('السبب: '.$subscription->admin_notes);
        }

        return $message
            ->line('تقدر ترفع إيصال جديد من صفحة الاشتراك، أو تكلّمنا ونحل الموضوع سوا.')
            ->action('صفحة الاشتراك', $subscription->is_bundle || ! $subscription->game_id ? route('subscribe.bundle') : route('subscribe.create', $subscription->game_id))
            ->salutation($site);
    }
}
