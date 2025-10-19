<?php

namespace App\Contracts\Communication;

enum CommunicationStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case FAILED = 'failed';
    case BOUNCED = 'bounced';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable description
     */
    public function description(): string
    {
        return match($this) {
            self::PENDING => 'Message is queued for sending',
            self::SENT => 'Message has been sent to provider',
            self::DELIVERED => 'Message delivered to recipient',
            self::READ => 'Message has been read by recipient',
            self::FAILED => 'Message delivery failed',
            self::BOUNCED => 'Message bounced back',
            self::REJECTED => 'Message was rejected by provider',
            self::EXPIRED => 'Message expired before delivery',
            self::CANCELLED => 'Message was cancelled',
        };
    }

    /**
     * Check if status indicates success
     */
    public function isSuccessful(): bool
    {
        return in_array($this, [
            self::SENT,
            self::DELIVERED,
            self::READ,
        ]);
    }

    /**
     * Check if status indicates failure
     */
    public function isFailed(): bool
    {
        return in_array($this, [
            self::FAILED,
            self::BOUNCED,
            self::REJECTED,
            self::EXPIRED,
        ]);
    }

    /**
     * Check if status indicates completion (final state)
     */
    public function isComplete(): bool
    {
        return $this !== self::PENDING;
    }

    /**
     * Get status color for UI
     */
    public function getColor(): string
    {
        return match($this) {
            self::PENDING => 'yellow',
            self::SENT => 'blue',
            self::DELIVERED => 'green',
            self::READ => 'green',
            self::FAILED, self::BOUNCED, self::REJECTED => 'red',
            self::EXPIRED, self::CANCELLED => 'gray',
        };
    }

    /**
     * Get all final statuses
     */
    public static function finalStatuses(): array
    {
        return [
            self::DELIVERED,
            self::READ,
            self::FAILED,
            self::BOUNCED,
            self::REJECTED,
            self::EXPIRED,
            self::CANCELLED,
        ];
    }
}
