<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class IdGenerator
{
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
