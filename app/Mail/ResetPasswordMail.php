<?php
namespace App\Mail;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordMail extends Notification
{
    public $token;
    public $url;

    public function __construct($token, $url)
    {
        $this->token = $token;
        $this->url   = $url;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->line('You requested a password reset.')
            ->action('Reset Password', $this->url)
            ->line('If you did not request a password reset, no further action is required.');
    }
}
