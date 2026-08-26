<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('メールアドレス確認のお知らせ')
            ->greeting($notifiable->name . ' さん')
            ->line('ご登録ありがとうございます。')
            ->line('下のボタンをクリックして、メールアドレスの確認を完了してください。')
            ->action('メールアドレスを確認する', $verificationUrl)
            ->line('このメールに心当たりがない場合は、何もする必要はありません。')
            ->salutation('イベント管理サイト');
    }

    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(
                Config::get('auth.verification.expire', 60)
            ),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}