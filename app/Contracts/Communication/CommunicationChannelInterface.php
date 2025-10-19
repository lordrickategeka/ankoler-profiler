<?php

namespace App\Contracts\Communication;

use App\Models\Person;
use Illuminate\Support\Collection;

interface CommunicationChannelInterface
{
    /**
     * Send a single message to a recipient
     *
     * @param string $recipient The recipient identifier (email, phone number, etc.)
     * @param string $message The message content
     * @param array $options Additional options for the message
     * @return CommunicationResult
     */
    public function send(string $recipient, string $message, array $options = []): CommunicationResult;

    /**
     * Send bulk messages to multiple recipients
     *
     * @param array $recipients Array of recipient identifiers
     * @param string $message The message content
     * @param array $options Additional options for the message
     * @return Collection<CommunicationResult>
     */
    public function sendBulk(array $recipients, string $message, array $options = []): Collection;

    /**
     * Send personalized messages to multiple recipients
     *
     * @param Collection<Person> $persons Collection of Person models
     * @param string $template Message template with placeholders
     * @param array $options Additional options for the message
     * @return Collection<CommunicationResult>
     */
    public function sendPersonalized(Collection $persons, string $template, array $options = []): Collection;

    /**
     * Get delivery status of a message
     *
     * @param string $messageId The unique message identifier
     * @return CommunicationStatus
     */
    public function getDeliveryStatus(string $messageId): CommunicationStatus;

    /**
     * Get the channel type (email, sms, whatsapp)
     *
     * @return string
     */
    public function getChannelType(): string;

    /**
     * Check if the channel is available and configured
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get channel-specific configuration requirements
     *
     * @return array
     */
    public function getConfigurationRequirements(): array;

    /**
     * Validate recipient format for this channel
     *
     * @param string $recipient
     * @return bool
     */
    public function validateRecipient(string $recipient): bool;

    /**
     * Get maximum message length for this channel
     *
     * @return int
     */
    public function getMaxMessageLength(): int;

    /**
     * Get supported message types for this channel
     *
     * @return array
     */
    public function getSupportedMessageTypes(): array;
}
