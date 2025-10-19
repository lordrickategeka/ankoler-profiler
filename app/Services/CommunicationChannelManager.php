<?php

namespace App\Services;

use App\Contracts\Communication\CommunicationChannelInterface;
use App\Contracts\Communication\CommunicationMessage;
use App\Contracts\Communication\CommunicationResult;
use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CommunicationChannelManager
{
    protected array $channels = [];

    /**
     * Register a communication channel
     */
    public function registerChannel(string $name, CommunicationChannelInterface $channel): void
    {
        $this->channels[$name] = $channel;
    }

    /**
     * Get a registered channel
     */
    public function getChannel(string $name): ?CommunicationChannelInterface
    {
        return $this->channels[$name] ?? null;
    }

    /**
     * Get all registered channels
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * Check if a channel is available
     */
    public function isChannelAvailable(string $name): bool
    {
        $channel = $this->getChannel($name);
        return $channel && $channel->isAvailable();
    }

    /**
     * Send a message using the specified channel
     */
    public function send(CommunicationMessage $message): CommunicationResult
    {
        $channel = $this->getChannel($message->channel);
        
        if (!$channel) {
            return CommunicationResult::failure(
                messageId: 'CHAN_NOT_FOUND_' . time(),
                recipient: $message->recipient,
                channel: $message->channel,
                errorMessage: "Communication channel '{$message->channel}' is not registered"
            );
        }

        if (!$channel->isAvailable()) {
            return CommunicationResult::failure(
                messageId: 'CHAN_UNAVAILABLE_' . time(),
                recipient: $message->recipient,
                channel: $message->channel,
                errorMessage: "Communication channel '{$message->channel}' is not available"
            );
        }

        try {
            return $channel->send(
                recipient: $message->recipient,
                message: $message->content,
                options: array_merge($message->options, [
                    'subject' => $message->subject,
                    'attachments' => $message->attachments,
                    'template' => $message->template,
                    'template_data' => $message->templateData,
                    'priority' => $message->priority,
                    'metadata' => $message->metadata,
                ])
            );
        } catch (\Exception $e) {
            Log::error('Error sending message via channel', [
                'channel' => $message->channel,
                'recipient' => $message->recipient,
                'error' => $e->getMessage()
            ]);

            return CommunicationResult::failure(
                messageId: 'SEND_ERROR_' . time(),
                recipient: $message->recipient,
                channel: $message->channel,
                errorMessage: 'Failed to send message: ' . $e->getMessage()
            );
        }
    }

    /**
     * Send bulk messages using the specified channel
     */
    public function sendBulk(string $channelName, array $recipients, string $message, array $options = []): Collection
    {
        $channel = $this->getChannel($channelName);
        
        if (!$channel) {
            return collect($recipients)->map(function ($recipient) use ($channelName) {
                return CommunicationResult::failure(
                    messageId: 'BULK_CHAN_NOT_FOUND_' . time(),
                    recipient: $recipient,
                    channel: $channelName,
                    errorMessage: "Communication channel '{$channelName}' is not registered"
                );
            });
        }

        if (!$channel->isAvailable()) {
            return collect($recipients)->map(function ($recipient) use ($channelName) {
                return CommunicationResult::failure(
                    messageId: 'BULK_CHAN_UNAVAILABLE_' . time(),
                    recipient: $recipient,
                    channel: $channelName,
                    errorMessage: "Communication channel '{$channelName}' is not available"
                );
            });
        }

        return $channel->sendBulk($recipients, $message, $options);
    }

    /**
     * Send personalized messages using the specified channel
     */
    public function sendPersonalized(string $channelName, Collection $persons, string $template, array $options = []): Collection
    {
        $channel = $this->getChannel($channelName);
        
        if (!$channel) {
            return $persons->map(function ($person) use ($channelName) {
                return CommunicationResult::failure(
                    messageId: 'PERS_CHAN_NOT_FOUND_' . time(),
                    recipient: $person->person_id ?? 'unknown',
                    channel: $channelName,
                    errorMessage: "Communication channel '{$channelName}' is not registered"
                );
            });
        }

        if (!$channel->isAvailable()) {
            return $persons->map(function ($person) use ($channelName) {
                return CommunicationResult::failure(
                    messageId: 'PERS_CHAN_UNAVAILABLE_' . time(),
                    recipient: $person->person_id ?? 'unknown',
                    channel: $channelName,
                    errorMessage: "Communication channel '{$channelName}' is not available"
                );
            });
        }

        return $channel->sendPersonalized($persons, $template, $options);
    }

    /**
     * Get channel information for all registered channels
     */
    public function getChannelInfo(): array
    {
        $info = [];
        
        foreach ($this->channels as $name => $channel) {
            $info[$name] = [
                'type' => $channel->getChannelType(),
                'available' => $channel->isAvailable(),
                'max_message_length' => $channel->getMaxMessageLength(),
                'supported_message_types' => $channel->getSupportedMessageTypes(),
                'configuration_requirements' => $channel->getConfigurationRequirements(),
            ];

            // Add additional capabilities if the channel supports them
            if (method_exists($channel, 'getCapabilities')) {
                $info[$name]['capabilities'] = $channel->getCapabilities();
            }

            if (method_exists($channel, 'getPricingInfo')) {
                $info[$name]['pricing'] = $channel->getPricingInfo();
            }
        }
        
        return $info;
    }

    /**
     * Get available channels (only those that are properly configured)
     */
    public function getAvailableChannels(): array
    {
        return array_filter($this->channels, function ($channel) {
            return $channel->isAvailable();
        });
    }

    /**
     * Validate a recipient for a specific channel
     */
    public function validateRecipient(string $channelName, string $recipient): bool
    {
        $channel = $this->getChannel($channelName);
        return $channel ? $channel->validateRecipient($recipient) : false;
    }
}