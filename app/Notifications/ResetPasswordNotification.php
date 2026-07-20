<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    use Queueable;

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Reset Password Akun')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Kami menerima permintaan untuk mereset password akun Anda.')
            ->action('Reset Password', $resetUrl)
            ->line('Tautan ini hanya berlaku sementara sesuai pengaturan keamanan sistem.')
            ->line('Jika Anda tidak merasa meminta reset password, abaikan email ini.');
    }
}
