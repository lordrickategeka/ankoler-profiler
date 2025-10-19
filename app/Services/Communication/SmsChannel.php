<?php

namespace App\Services\Communication;

use App\Contracts\Communication\CommunicationChannelInterface;
use App\Contracts\Communication\CommunicationResult;
use App\Contracts\Communication\CommunicationStatus;
use App\Models\Person;
use App\Services\DirectAfricasTalkingSmsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SmsChannel implements CommunicationChannelInterface
{
    protected DirectAfricasTalkingSmsService $smsService;
    protected array $config;

    public function __construct(DirectAfricasTalkingSmsService $smsService)
    {
        $this->smsService = $smsService;
        $this->config = config('africastalking');
    }

    /**
     * Send a single SMS message to a recipient
     */
    public function send(string $recipient, string $message, array $options = []): CommunicationResult
    {
        // Validate message length
        if (strlen($message) > $this->getMaxMessageLength()) {
            return CommunicationResult::failure(
                messageId: 'SMS_INVALID_' . time(),
                recipient: $recipient,
                channel: 'sms',
                errorMessage: 'Message exceeds maximum length of ' . $this->getMaxMessageLength() . ' characters'
            );
        }

        return $this->smsService->sendSms($recipient, $message, $options);
    }

    /**
     * Send bulk messages to multiple recipients
     */
    public function sendBulk(array $recipients, string $message, array $options = []): Collection
    {
        $results = $this->smsService->sendBulkSms($recipients, $message, $options);
        
        return collect($results);
    }

    /**
     * Send personalized messages to multiple recipients
     */
    public function sendPersonalized(Collection $persons, string $template, array $options = []): Collection
    {
        $results = collect();

        foreach ($persons as $person) {
            try {
                // Get phone number for the person
                $phoneNumber = $this->getPersonPhoneNumber($person);
                
                if (!$phoneNumber) {
                    $results->push(CommunicationResult::failure(
                        messageId: 'SMS_NO_PHONE_' . time(),
                        recipient: $person->person_id ?? 'unknown',
                        channel: 'sms',
                        errorMessage: 'No valid phone number found for person'
                    ));
                    continue;
                }

                // Replace placeholders in template with person data
                $personalizedMessage = $this->replacePersonPlaceholders($template, $person, $options);

                // Send the personalized message
                $result = $this->send($phoneNumber, $personalizedMessage, $options);
                $results->push($result);

            } catch (\Exception $e) {
                Log::error('Error sending personalized SMS', [
                    'person_id' => $person->id ?? 'unknown',
                    'error' => $e->getMessage()
                ]);

                $results->push(CommunicationResult::failure(
                    messageId: 'SMS_ERROR_' . time(),
                    recipient: $person->person_id ?? 'unknown',
                    channel: 'sms',
                    errorMessage: 'Error processing personalized message: ' . $e->getMessage()
                ));
            }
        }

        return $results;
    }

    /**
     * Get delivery status of a message
     */
    public function getDeliveryStatus(string $messageId): CommunicationStatus
    {
        return $this->smsService->getDeliveryStatus($messageId);
    }

    /**
     * Get the channel type
     */
    public function getChannelType(): string
    {
        return 'sms';
    }

    /**
     * Check if the channel is available and configured
     */
    public function isAvailable(): bool
    {
        try {
            // Check if required configuration is present
            if (empty($this->config['username']) || empty($this->config['api_key'])) {
                return false;
            }

            // Optional: Test connectivity by checking account balance
            $balanceResult = $this->smsService->getAccountBalance();
            
            return $balanceResult['success'] ?? false;
        } catch (\Exception $e) {
            Log::warning('SMS channel availability check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get channel-specific configuration requirements
     */
    public function getConfigurationRequirements(): array
    {
        return [
            'required' => [
                'AFRICASTALKING_USERNAME' => 'Africa\'s Talking username',
                'AFRICASTALKING_API_KEY' => 'Africa\'s Talking API key',
            ],
            'optional' => [
                'AFRICASTALKING_ENVIRONMENT' => 'Environment (sandbox/production)',
                'AFRICASTALKING_SENDER_ID' => 'Custom sender ID',
                'AFRICASTALKING_DELIVERY_REPORTS' => 'Enable delivery reports',
            ],
            'documentation' => 'https://developers.africastalking.com/docs/sms/overview'
        ];
    }

    /**
     * Validate recipient format for SMS (phone number)
     */
    public function validateRecipient(string $recipient): bool
    {
        return $this->smsService->validatePhoneNumber($recipient);
    }

    /**
     * Get maximum message length for SMS
     */
    public function getMaxMessageLength(): int
    {
        return $this->config['sms']['max_length'] ?? 160;
    }

    /**
     * Get supported message types for SMS
     */
    public function getSupportedMessageTypes(): array
    {
        return [
            'text' => [
                'name' => 'Plain Text',
                'description' => 'Standard text messages up to 160 characters',
                'max_length' => $this->getMaxMessageLength(),
            ],
            'unicode' => [
                'name' => 'Unicode Text',
                'description' => 'Messages with special characters (reduced length)',
                'max_length' => 70,
            ],
        ];
    }

    /**
     * Get additional SMS channel capabilities
     */
    public function getCapabilities(): array
    {
        return [
            'bulk_messaging' => true,
            'personalized_messaging' => true,
            'delivery_reports' => $this->config['sms']['delivery_reports'] ?? false,
            'sender_id_support' => true,
            'unicode_support' => true,
            'max_recipients_per_bulk' => $this->config['sms']['max_recipients'] ?? 100,
        ];
    }

    /**
     * Get SMS pricing information
     */
    public function getPricingInfo(): array
    {
        return [
            'currency' => 'USD',
            'model' => 'pay_per_message',
            'rates' => [
                'local' => 'Varies by country',
                'international' => 'Varies by destination',
            ],
            'note' => 'Actual pricing depends on destination country and message type',
        ];
    }

    /**
     * Get account information
     */
    public function getAccountInfo(): array
    {
        return $this->smsService->getAccountBalance();
    }

    /**
     * Extract phone number from Person model
     */
    protected function getPersonPhoneNumber(Person $person): ?string
    {
        // Try to get primary phone number
        $primaryPhone = $person->primaryPhone();
        if ($primaryPhone && $primaryPhone->number) {
            return $this->smsService->formatPhoneNumber($primaryPhone->number);
        }

        // Fallback to any available phone number
        $phone = $person->phoneNumbers()->first();
        if ($phone && $phone->number) {
            return $this->smsService->formatPhoneNumber($phone->number);
        }

        return null;
    }

    /**
     * Replace person placeholders in message template
     */
    protected function replacePersonPlaceholders(string $template, Person $person, array $options = []): string
    {
        // Get person variables
        $variables = $this->getPersonVariables($person);
        
        // Merge with any additional options
        $variables = array_merge($variables, $options);

        // Replace placeholders
        $message = $template;
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $message = str_replace($placeholder, (string) $value, $message);
        }

        return $message;
    }

    /**
     * Get person variables for template replacement
     */
    protected function getPersonVariables(Person $person): array
    {
        $variables = [
            'given_name' => $person->given_name,
            'family_name' => $person->family_name,
            'full_name' => trim($person->given_name . ' ' . $person->family_name),
            'person_id' => $person->person_id,
        ];

        // Add organization information if available
        $affiliation = $person->affiliations->first();
        if ($affiliation && $affiliation->organisation) {
            $variables['organization_name'] = $affiliation->organisation->legal_name;
            $variables['role_title'] = $affiliation->role_title;
        }

        // Add contact information
        $primaryPhone = $person->primaryPhone();
        if ($primaryPhone) {
            $variables['phone_number'] = $primaryPhone->number;
        }

        $primaryEmail = $person->primaryEmail();
        if ($primaryEmail) {
            $variables['email_address'] = $primaryEmail->email;
        }

        return $variables;
    }
}