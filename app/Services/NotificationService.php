<?php

namespace App\Services;

use App\Models\Person;
use App\Notifications\ProfiledNotification;

class NotificationService
{
    public static function notifyProfiled(Person $person, $temporaryPassword = null)
    {
        $person->notify(new ProfiledNotification($temporaryPassword));
    }
}
