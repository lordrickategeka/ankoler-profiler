<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
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
        $temporaryPassword = null;
        try {
            $key = 'temp_password_user_' . $user->id;
            if (Cache::has($key)) {
                try {
                    $temporaryPassword = Crypt::decryptString(Cache::get($key));
                    Cache::forget($key);
                } catch (\Exception $e) {
                    Log::warning('Failed to decrypt temporary password from cache', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Cache check failed for temporary password', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        if ($user && $organization) {
            Log::info('SendWelcomeEmailAfterVerification listener processing', ['user_id' => $user->id, 'organization_id' => $organization->id]);
            // Queue the welcome email so it is handled reliably by the queue worker
            Mail::to($user->email)->queue(new AdminWelcomeEmail($user, $organization, $temporaryPassword));
            Log::info('AdminWelcomeEmail queued', ['user_id' => $user->id, 'email' => $user->email]);
        } else {
            Log::warning('SendWelcomeEmailAfterVerification missing user or organization', ['user' => $user ? $user->id : null]);
        }
    }
}
