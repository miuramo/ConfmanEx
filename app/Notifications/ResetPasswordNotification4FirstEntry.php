<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification4FirstEntry extends Notification
{
    use Queueable;

    private string $token;
    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/reset-password/'.$this->token."?email=".str_replace("@","%40",$notifiable->email));
        return (new MailMessage)
                    ->subject("メールアドレスの認証を行ってください")
                    ->greeting(null)
                    ->line("# メールアドレスの認証を行ってください")
                    ->line('下のボタンを押して、メールアドレスの認証を行ってください。')
                    ->action('メールアドレスの認証とパスワードの設定', $url);
                    // ->line('Thank you for using our application!')
                    // ->line($url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
