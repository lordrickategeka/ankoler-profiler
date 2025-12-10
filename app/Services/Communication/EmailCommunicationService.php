<?php

namespace App\Services\Communication;

use App\Contracts\Communication\CommunicationChannelInterface;
use App\Contracts\Communication\CommunicationResult;
use App\Contracts\Communication\CommunicationStatus;
use App\Models\Person;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class EmailCommunicationService implements CommunicationChannelInterface
{
    public function send(string $recipient, string $message, array $options = []): CommunicationResult
    {
        try {
            $messageId = $this->generateMessageId();

            $subject = $options['subject'] ?? 'Message from ' . config('app.name');
            $from = $options['from'] ?? config('mail.from.address');
            $fromName = $options['from_name'] ?? config('mail.from.name');

            Mail::send([], [], function ($mail) use ($recipient, $subject, $message, $from, $fromName) {
                $mail->to($recipient)
                     ->subject($subject)
                     ->from($from, $fromName)
                     ->html($this->formatMessage($message));
            });

            return CommunicationResult::success(
                messageId: $messageId,
                recipient: $recipient,
                channel: 'email',
                metadata: [
                    'subject' => $subject,
                    'from' => $from,
                    'from_name' => $fromName,
                ]
            );

        } catch (Exception $e) {
            Log::error('Email sending failed', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return CommunicationResult::failure(
                messageId: $this->generateMessageId(),
                recipient: $recipient,
                channel: 'email',
                errorMessage: $e->getMessage()
            );
        }
    }

    public function sendBulk(array $recipients, string $message, array $options = []): Collection
    {
        $results = collect();

        foreach ($recipients as $recipient) {
            $results->push($this->send($recipient, $message, $options));
        }

        return $results;
    }

    public function sendPersonalized(Collection $persons, string $template, array $options = []): Collection
    {
        $results = collect();

        foreach ($persons as $person) {
            $email = $person->emailAddresses->where('is_primary', true)->first()?->email;

            if (!$email) {
                $results->push(CommunicationResult::failure(
                    messageId: $this->generateMessageId(),
                    recipient: 'unknown',
                    channel: 'email',
                    errorMessage: 'No primary email address found for person: ' . $person->full_name
                ));
                continue;
            }

            $personalizedMessage = $this->personalizeMessage($template, $person);
            $personalizedOptions = $this->personalizeOptions($options, $person);

            $results->push($this->send($email, $personalizedMessage, $personalizedOptions));
        }

        return $results;
    }

    public function getDeliveryStatus(string $messageId): CommunicationStatus
    {
        // For now, return SENT status. In production, this would query
        // the email provider's API or webhook data
        return CommunicationStatus::SENT;
    }

    public function getChannelType(): string
    {
        return 'email';
    }

    public function isAvailable(): bool
    {
        try {
            $mailer = config('mail.default');
            $host = config('mail.mailers.' . $mailer . '.host');
            $from = config('mail.from.address');

            return !empty($mailer) && !empty($host) && !empty($from);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getConfigurationRequirements(): array
    {
        return [
            'MAIL_MAILER' => 'Email driver (smtp, sendmail, etc.)',
            'MAIL_HOST' => 'SMTP host server',
            'MAIL_PORT' => 'SMTP port',
            'MAIL_USERNAME' => 'SMTP username',
            'MAIL_PASSWORD' => 'SMTP password',
            'MAIL_FROM_ADDRESS' => 'From email address',
            'MAIL_FROM_NAME' => 'From name',
        ];
    }

    public function validateRecipient(string $recipient): bool
    {
        return filter_var($recipient, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getMaxMessageLength(): int
    {
        // Email has no practical character limit for content
        return PHP_INT_MAX;
    }

    public function getSupportedMessageTypes(): array
    {
        return ['text', 'html', 'markdown'];
    }

    /**
     * Generate a unique message ID
     */
    private function generateMessageId(): string
    {
        return 'email_' . time() . '_' . Str::random(10);
    }

    /**
     * Format message content for email
     */
    private function formatMessage(string $message): string
    {
        // Convert plain text to HTML with basic formatting
        $html = nl2br(htmlspecialchars($message));

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Message</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='content'>
                    {$html}
                </div>
                <div class='footer'>
                    <p>This email was sent from " . config('app.name') . "</p>
                </div>
            </div>
        </body>
        </html>";
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
            '{email}' => $person->emailAddresses->where('is_primary', true)->first()?->email ?? '',
            '{phone}' => $person->phones->where('is_primary', true)->first()?->number ?? '',
            '{organization}' => $person->currentAffiliation?->Organization?->display_name ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Personalize options with person data
     */
    private function personalizeOptions(array $options, Person $person): array
    {
        if (isset($options['subject'])) {
            $options['subject'] = $this->personalizeMessage($options['subject'], $person);
        }

        return $options;
    }
}
