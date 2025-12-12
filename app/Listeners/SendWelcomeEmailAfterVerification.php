<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminWelcomeEmail;
use App\Models\Organization;

class SendWelcomeEmailAfterVerification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(Verified $event)
    {
        $user = $event->user;
        // Get the user's organization (customize as needed)
        $organization = $user->person->organization ?? null;
        $temporaryPassword = null; // Not available here, unless you store it somewhere (e.g., in a custom field or notification)
        if ($user && $organization) {
            Mail::to($user->email)->send(new AdminWelcomeEmail($user, $organization, $temporaryPassword));
        }
    }
}
