<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $token
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(
            route(
                'password.reset',
                [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ],
                false
            )
        );

        return (new MailMessage)
            ->subject('パスワード再設定のお知らせ')
            ->greeting($notifiable->name . ' さん')
            ->line('パスワード再設定のリクエストを受け付けました。')
            ->line('下のボタンから、新しいパスワードを設定してください。')
            ->action('パスワードを再設定する', $url)
            ->line('このメールに心当たりがない場合は、何もする必要はありません。')
            ->salutation('イベント管理サイト');
    }
}