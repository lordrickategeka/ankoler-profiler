<?php

namespace App\Contracts\Communication;

class CommunicationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $messageId,
        public readonly string $recipient,
        public readonly string $channel,
        public readonly ?string $providerMessageId = null,
        public readonly ?string $errorMessage = null,
        public readonly ?array $metadata = null,
        public readonly ?\DateTime $sentAt = null
    ) {
    }

    /**
     * Create a successful result
     */
    public static function success(
        string $messageId,
        string $recipient,
        string $channel,
        ?string $providerMessageId = null,
        ?array $metadata = null
    ): self {
        return new self(
            success: true,
            messageId: $messageId,
            recipient: $recipient,
            channel: $channel,
            providerMessageId: $providerMessageId,
            metadata: $metadata,
            sentAt: new \DateTime()
        );
    }

    /**
     * Create a failed result
     */
    public static function failure(
        string $messageId,
        string $recipient,
        string $channel,
        string $errorMessage,
        ?array $metadata = null
    ): self {
        return new self(
            success: false,
            messageId: $messageId,
            recipient: $recipient,
            channel: $channel,
            errorMessage: $errorMessage,
            metadata: $metadata,
            sentAt: new \DateTime()
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message_id' => $this->messageId,
            'recipient' => $this->recipient,
            'channel' => $this->channel,
            'provider_message_id' => $this->providerMessageId,
            'error_message' => $this->errorMessage,
            'metadata' => $this->metadata,
            'sent_at' => $this->sentAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check if the result is successful
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Check if the result failed
     */
    public function isFailed(): bool
    {
        return !$this->success;
    }
}
