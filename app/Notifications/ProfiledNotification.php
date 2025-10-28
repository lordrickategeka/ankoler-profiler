<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SmsMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ProfiledNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast']; // Add 'sms' if using a package
    }

    public $temporaryPassword;

    public function __construct($temporaryPassword = null)
    {
        $this->temporaryPassword = $temporaryPassword;
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('You are now enrolled on the platform')
            ->line('Your profile has been created by your organisation.');

        if ($this->temporaryPassword) {
            $mail->line('Your temporary password for signing in is: **' . $this->temporaryPassword . '**')
                 ->line('Please change your password after your first login.');
        }

        $mail->action('Apply to Community Units', url('/units'))
            ->line('You can also submit your talent/product for showcasing or join professional clusters.');

        return $mail;
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'You are now enrolled on the platform',
            'actions' => [
                ['label' => 'Apply to Community Units', 'url' => url('/units')],
                ['label' => 'Showcase Talent/Product', 'url' => url('/showcase')],
                ['label' => 'Join Clusters', 'url' => url('/clusters')],
            ]
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'You are now enrolled on the platform',
        ]);
    }

    // Add SMS/WhatsApp logic if needed
}
