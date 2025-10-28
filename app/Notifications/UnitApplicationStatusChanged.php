<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\PersonAffiliation;

class UnitApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $application;

    public function __construct(PersonAffiliation $application)
    {
        $this->application = $application;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $status = ucfirst($this->application->status);
        $unit = $this->application->organizationUnit->name ?? 'Unit';
        $lines = [
            'Your application to join "' . $unit . '" has been ' . $status . '.',
        ];
        if ($this->application->status === 'active') {
            $lines[] = 'You are now a member of this unit.';
        } elseif ($this->application->status === 'terminated') {
            $lines[] = 'Your application was rejected.';
        }
        return (new MailMessage)
            ->subject('Unit Membership Application ' . $status)
            ->greeting('Hello,')
            ->line(...$lines)
            ->line('Thank you for using our system!');
    }
}
