<?php

namespace App\Services\Communication;

use App\Contracts\Communication\CommunicationChannelInterface;
use App\Contracts\Communication\CommunicationResult;
use App\Contracts\Communication\CommunicationStatus;
use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class WhatsAppCommunicationService implements CommunicationChannelInterface
{
    private string $provider;
    private array $config;

    public function __construct()
    {
        $this->provider = config('services.whatsapp.default', 'twilio');
        $this->config = config('services.whatsapp.providers.' . $this->provider, []);
    }

    public function send(string $recipient, string $message, array $options = []): CommunicationResult
    {
        try {
            $messageId = $this->generateMessageId();

            // Normalize phone number for WhatsApp
            $normalizedRecipient = $this->normalizeWhatsAppNumber($recipient);

            if (!$this->validateRecipient($normalizedRecipient)) {
                return CommunicationResult::failure(
                    messageId: $messageId,
                    recipient: $recipient,
                    channel: 'whatsapp',
                    errorMessage: 'Invalid WhatsApp number format'
                );
            }

            // Check message length
            if (strlen($message) > $this->getMaxMessageLength()) {
                return CommunicationResult::failure(
                    messageId: $messageId,
                    recipient: $recipient,
                    channel: 'whatsapp',
                    errorMessage: 'Message exceeds maximum length of ' . $this->getMaxMessageLength() . ' characters'
                );
            }

            $result = $this->sendViaProvider($normalizedRecipient, $message, $options);

            if ($result['success']) {
                return CommunicationResult::success(
                    messageId: $messageId,
                    recipient: $normalizedRecipient,
                    channel: 'whatsapp',
                    providerMessageId: $result['provider_message_id'] ?? null,
                    metadata: [
                        'provider' => $this->provider,
                        'message_type' => $options['type'] ?? 'text',
                        'template_name' => $options['template_name'] ?? null,
                    ]
                );
            } else {
                return CommunicationResult::failure(
                    messageId: $messageId,
                    recipient: $normalizedRecipient,
                    channel: 'whatsapp',
                    errorMessage: $result['error']
                );
            }

        } catch (Exception $e) {
            Log::error('WhatsApp sending failed', [
                'recipient' => $recipient,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return CommunicationResult::failure(
                messageId: $this->generateMessageId(),
                recipient: $recipient,
                channel: 'whatsapp',
                errorMessage: $e->getMessage()
            );
        }
    }

    public function sendBulk(array $recipients, string $message, array $options = []): Collection
    {
        $results = collect();

        // WhatsApp typically doesn't support bulk messaging due to rate limits
        // Send individually with delays
        foreach ($recipients as $index => $recipient) {
            $results->push($this->send($recipient, $message, $options));

            // Add delay to respect rate limits (WhatsApp is strict about this)
            if ($index < count($recipients) - 1) {
                usleep(1000000); // 1 second delay
            }
        }

        return $results;
    }

    public function sendPersonalized(Collection $persons, string $template, array $options = []): Collection
    {
        $results = collect();

        foreach ($persons as $person) {
            $phone = $person->phones->where('is_primary', true)->first()?->number;

            if (!$phone) {
                $results->push(CommunicationResult::failure(
                    messageId: $this->generateMessageId(),
                    recipient: 'unknown',
                    channel: 'whatsapp',
                    errorMessage: 'No primary phone number found for person: ' . $person->full_name
                ));
                continue;
            }

            $personalizedMessage = $this->personalizeMessage($template, $person);

            $results->push($this->send($phone, $personalizedMessage, $options));

            // Add delay between personalized messages
            usleep(1000000); // 1 second delay
        }

        return $results;
    }

    public function getDeliveryStatus(string $messageId): CommunicationStatus
    {
        // This would query the provider's API for delivery status
        // WhatsApp provides detailed delivery statuses
        return CommunicationStatus::SENT;
    }

    public function getChannelType(): string
    {
        return 'whatsapp';
    }

    public function isAvailable(): bool
    {
        return !empty($this->config) &&
               isset($this->config['api_key']) &&
               !empty($this->config['api_key']) &&
               isset($this->config['from_number']) &&
               !empty($this->config['from_number']);
    }

    public function getConfigurationRequirements(): array
    {
        return [
            'WHATSAPP_PROVIDER' => 'WhatsApp provider (twilio, meta, etc.)',
            'WHATSAPP_API_KEY' => 'API key for the WhatsApp provider',
            'WHATSAPP_FROM_NUMBER' => 'WhatsApp Business number',
            'WHATSAPP_ACCESS_TOKEN' => 'Access token (if required)',
            'WHATSAPP_VERIFY_TOKEN' => 'Webhook verify token',
        ];
    }

    public function validateRecipient(string $recipient): bool
    {
        // WhatsApp numbers must be in international format
        return preg_match('/^\+?[1-9]\d{1,14}$/', $recipient);
    }

    public function getMaxMessageLength(): int
    {
        return 4096; // WhatsApp message limit
    }

    public function getSupportedMessageTypes(): array
    {
        return ['text', 'template', 'media', 'document', 'location'];
    }

    /**
     * Send a WhatsApp template message
     */
    public function sendTemplate(string $recipient, string $templateName, array $parameters = [], array $options = []): CommunicationResult
    {
        $options['type'] = 'template';
        $options['template_name'] = $templateName;
        $options['template_parameters'] = $parameters;

        return $this->send($recipient, '', $options);
    }

    /**
     * Generate a unique message ID
     */
    private function generateMessageId(): string
    {
        return 'whatsapp_' . time() . '_' . Str::random(10);
    }

    /**
     * Normalize phone number for WhatsApp
     */
    private function normalizeWhatsAppNumber(string $phone): string
    {
        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);

        // Add + if not present
        if (!str_starts_with($cleaned, '+')) {
            // If number starts with 256 (Uganda), add +
            if (str_starts_with($cleaned, '256')) {
                $cleaned = '+' . $cleaned;
            }
            // If number starts with 07 (local Uganda), convert to +256
            elseif (str_starts_with($cleaned, '07')) {
                $cleaned = '+256' . substr($cleaned, 1);
            }
            // If number starts with 7 (local Uganda without 0), convert to +256
            elseif (str_starts_with($cleaned, '7') && strlen($cleaned) === 9) {
                $cleaned = '+256' . $cleaned;
            }
            // Otherwise assume it needs Uganda country code
            else {
                $cleaned = '+256' . ltrim($cleaned, '0');
            }
        }

        return $cleaned;
    }

    /**
     * Send WhatsApp message via the configured provider
     */
    private function sendViaProvider(string $recipient, string $message, array $options): array
    {
        switch ($this->provider) {
            case 'twilio':
                return $this->sendViaTwilio($recipient, $message, $options);
            case 'meta':
                return $this->sendViaMeta($recipient, $message, $options);
            default:
                return [
                    'success' => false,
                    'error' => 'Unsupported WhatsApp provider: ' . $this->provider
                ];
        }
    }

    /**
     * Send WhatsApp message via Twilio
     */
    private function sendViaTwilio(string $recipient, string $message, array $options): array
    {
        try {
            $data = [
                'From' => 'whatsapp:' . $this->config['from_number'],
                'To' => 'whatsapp:' . $recipient,
            ];

            // Handle different message types
            if (isset($options['type']) && $options['type'] === 'template') {
                // Template message
                $data['ContentSid'] = $options['template_sid'];
                if (isset($options['template_parameters'])) {
                    $data['ContentVariables'] = json_encode($options['template_parameters']);
                }
            } else {
                // Regular text message
                $data['Body'] = $message;
            }

            $response = Http::withBasicAuth($this->config['account_sid'], $this->config['auth_token'])
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->config['account_sid']}/Messages.json", $data);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['sid'])) {
                return [
                    'success' => true,
                    'provider_message_id' => $responseData['sid'],
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Unknown Twilio WhatsApp error'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Twilio WhatsApp API error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send WhatsApp message via Meta (Facebook) API
     */
    private function sendViaMeta(string $recipient, string $message, array $options): array
    {
        try {
            $phoneNumberId = $this->config['phone_number_id'];
            $accessToken = $this->config['access_token'];

            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $recipient,
            ];

            // Handle different message types
            if (isset($options['type']) && $options['type'] === 'template') {
                $data['type'] = 'template';
                $data['template'] = [
                    'name' => $options['template_name'],
                    'language' => ['code' => $options['language'] ?? 'en'],
                ];

                if (isset($options['template_parameters'])) {
                    $data['template']['components'] = [
                        [
                            'type' => 'body',
                            'parameters' => array_map(fn($param) => ['type' => 'text', 'text' => $param], $options['template_parameters'])
                        ]
                    ];
                }
            } else {
                $data['type'] = 'text';
                $data['text'] = ['body' => $message];
            }

            $response = Http::withToken($accessToken)
                ->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", $data);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['messages'][0]['id'])) {
                return [
                    'success' => true,
                    'provider_message_id' => $responseData['messages'][0]['id'],
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Unknown Meta WhatsApp error'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Meta WhatsApp API error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Personalize message with person data
     */
    private function personalizeMessage(string $template, Person $person): string
    {
        $replacements = [
            '{first_name}' => $person->given_name,
            '{last_name}' => $person->family_name,
            '{full_name}' => $person->full_name,
            '{phone}' => $person->phones->where('is_primary', true)->first()?->number ?? '',
            '{organization}' => $person->currentAffiliation?->organisation?->display_name ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
