<?php

namespace App\Helpers;

use Illuminate\Support\Str;


class IdGenerator
{
    /**
     * Generate a unique Email ID (e.g., EML-251026DX-0001)
     *
     * @return string
     */
    public static function generateEmailId(): string
    {
        $prefix = 'EML-';
        $date = now()->format('ymd'); // e.g., 251026 for 2025-10-26
        $rand = strtoupper(Str::random(2));
        $dateRand = $date . $rand;
        // Find the max increment for today+rand
        $like = $prefix . $dateRand . '-%';
        $lastEmail = \App\Models\EmailAddress::where('email_id', 'like', $like)
            ->orderBy('email_id', 'desc')
            ->first();

        if ($lastEmail) {
            // Extract the increment part after the last dash
            $parts = explode('-', $lastEmail->email_id);
            $lastNumber = isset($parts[2]) ? (int)$parts[2] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $dateRand . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    /**
     * Generate a unique Phone ID (e.g., PHN-000001)
     *
     * @return string
     */
    public static function generatePhoneId(): string
    {
        $prefix = 'PHN-';
        $date = now()->format('ymd'); // e.g., 251026 for 2025-10-26
        $rand = strtoupper(Str::random(2));
        $dateRand = $date . $rand;
        // Find the max increment for today+rand
        $like = $prefix . $dateRand . '-%';
        $lastPhone = \App\Models\Phone::where('phone_id', 'like', $like)
            ->orderBy('phone_id', 'desc')
            ->first();

        if ($lastPhone) {
            // Extract the increment part after the last dash
            $parts = explode('-', $lastPhone->phone_id);
            $lastNumber = isset($parts[2]) ? (int)$parts[2] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $dateRand . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    /**
     * Generate a unique global identifier (e.g., GID-20251026-ABC123)
     *
     * @param string $prefix
     * @return string
     */
    public static function generateGlobalIdentifier(string $prefix = 'GID'): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));
        return strtoupper($prefix) . '-' . $date . '-' . $random;
    }
    /**
     * Generate a unique Person ID (e.g., PRS-000001)
     *
     * @return string
     */
    public static function generatePersonId(): string
    {
        // Find the max numeric part from existing person_ids
        $last = \App\Models\Person::where('person_id', 'like', 'PRS-%')
            ->orderByDesc('person_id')
            ->value('person_id');

        if ($last && preg_match('/PRS-(\\d+)/', $last, $matches)) {
            $num = (int)$matches[1] + 1;
        } else {
            $num = 1;
        }

        return 'PRS-' . str_pad($num, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a random alphanumeric ID
     *
     * @param int $length
     * @return string
     */
    public static function generate(int $length = 10): string
    {
        return Str::upper(Str::random($length));
    }

    /**
     * Generate a random numeric ID
     *
     * @param int $length
     * @return string
     */
    public static function numeric(int $length = 10): string
    {
        $id = '';
        for ($i = 0; $i < $length; $i++) {
            $id .= random_int(0, 9);
        }
        return $id;
    }

    /**
     * Generate a UUID
     *
     * @return string
     */
    public static function uuid(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Generate a ULID
     *
     * @return string
     */
    public static function ulid(): string
    {
        return (string) Str::ulid();
    }

    /**
     * Generate a prefixed ID (e.g., INV-ABC123)
     *
     * @param string $prefix
     * @param int $length
     * @return string
     */
    public static function prefixed(string $prefix, int $length = 8): string
    {
        return strtoupper($prefix) . '-' . Str::upper(Str::random($length));
    }

    /**
     * Generate a unique ID with timestamp
     *
     * @param string $prefix
     * @return string
     */
    public static function timestamped(string $prefix = ''): string
    {
        $timestamp = now()->format('YmdHis');
        $random = Str::upper(Str::random(6));

        return $prefix ? strtoupper($prefix) . '-' . $timestamp . '-' . $random
                       : $timestamp . '-' . $random;
    }

    /**
     * Generate a unique ID and ensure it doesn't exist in database
     *
     * @param string $model
     * @param string $column
     * @param int $length
     * @return string
     */
    public static function unique(string $model, string $column = 'id', int $length = 10): string
    {
        do {
            $id = self::generate($length);
        } while ($model::where($column, $id)->exists());

        return $id;
    }
}
