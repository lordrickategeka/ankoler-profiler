<?php

namespace App\Services\Communication;

use App\Contracts\Communication\CommunicationChannelInterface;
use App\Contracts\Communication\CommunicationResult;
use App\Contracts\Communication\CommunicationStatus;
use App\Services\DirectAfricasTalkingSmsService;
use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class SMSCommunicationService implements CommunicationChannelInterface
{
    protected DirectAfricasTalkingSmsService $smsService;

    public function __construct(DirectAfricasTalkingSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function send(string $recipient, string $message, array $options = []): CommunicationResult
    {
        try {
            // Use our DirectAfricasTalkingSmsService which handles everything properly
            return $this->smsService->sendSms($recipient, $message, $options);
            
        } catch (Exception $e) {
            Log::error('SMS sending failed via SMSCommunicationService', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return CommunicationResult::failure(
                messageId: Str::uuid()->toString(),
                recipient: $recipient,
                channel: 'sms',
                errorMessage: 'SMS sending error: ' . $e->getMessage()
            );
        }
    }

    public function sendBulk(array $recipients, string $message, array $options = []): Collection
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $results[] = $this->send($recipient, $message, $options);
        }
        
        return collect($results);
    }

    public function sendPersonalized(Collection $persons, string $template, array $options = []): Collection
    {
        $results = [];
        
        foreach ($persons as $person) {
            // Get phone number for person
            $phoneNumber = $this->getPersonPhoneNumber($person);
            
            if ($phoneNumber) {
                // Replace template variables
                $personalizedMessage = $this->replaceTemplateVariables($template, $person);
                $results[] = $this->send($phoneNumber, $personalizedMessage, $options);
            } else {
                $results[] = CommunicationResult::failure(
                    messageId: Str::uuid()->toString(),
                    recipient: $person->given_name . ' ' . $person->family_name,
                    channel: 'sms',
                    errorMessage: 'No valid phone number found'
                );
            }
        }
        
        return collect($results);
    }

    public function getDeliveryStatus(string $messageId): CommunicationStatus
    {
        return $this->smsService->getDeliveryStatus($messageId);
    }

    public function getChannelType(): string
    {
        return 'sms';
    }

    public function isAvailable(): bool
    {
        try {
            $validation = $this->smsService->validateConfiguration();
            return $validation['valid'];
        } catch (Exception $e) {
            return false;
        }
    }

    public function getConfigurationRequirements(): array
    {
        return [
            'AT_USERNAME' => 'Africa\'s Talking Username (sandbox for testing)',
            'AT_API_KEY' => 'Africa\'s Talking API Key',
            'AT_ENVIRONMENT' => 'Environment (sandbox or production)'
        ];
    }

    public function validateRecipient(string $recipient): bool
    {
        return $this->smsService->validatePhoneNumber($recipient);
    }

    public function getMaxMessageLength(): int
    {
        return 160; // Standard SMS length
    }

    public function getSupportedMessageTypes(): array
    {
        return ['text'];
    }

    /**
     * Get phone number from person model
     */
    private function getPersonPhoneNumber(Person $person): ?string
    {
        $phone = $person->phones->first();
        return $phone ? $this->smsService->formatPhoneNumber($phone->number) : null;
    }

    /**
     * Replace template variables with person data
     */
    private function replaceTemplateVariables(string $template, Person $person): string
    {
        $variables = [
            '{name}' => $person->given_name . ' ' . $person->family_name,
            '{first_name}' => $person->given_name,
            '{last_name}' => $person->family_name,
            '{given_name}' => $person->given_name,
            '{family_name}' => $person->family_name,
        ];

        return str_replace(array_keys($variables), array_values($variables), $template);
    }
}