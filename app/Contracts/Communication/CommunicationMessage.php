<?php

namespace App\Contracts\Communication;

class CommunicationMessage
{
    public function __construct(
        public readonly string $recipient,
        public readonly string $content,
        public readonly string $channel,
        public readonly array $options = [],
        public readonly ?string $subject = null,
        public readonly ?array $attachments = null,
        public readonly ?string $template = null,
        public readonly ?array $templateData = null,
        public readonly ?string $scheduledAt = null,
        public readonly ?int $priority = null,
        public readonly ?array $metadata = null
    ) {
    }

    /**
     * Create a simple message for any channel
     */
    public static function create(
        string $channel,
        string $recipient,
        string $content,
        array $options = []
    ): self {
        return new self(
            recipient: $recipient,
            content: $content,
            channel: $channel,
            options: $options
        );
    }

    /**
     * Create an email message
     */
    public static function email(
        string $recipient,
        string $content,
        ?string $subject = null,
        array $options = []
    ): self {
        return new self(
            recipient: $recipient,
            content: $content,
            channel: 'email',
            subject: $subject,
            options: $options
        );
    }

    /**
     * Create an SMS message
     */
    public static function sms(
        string $recipient,
        string $content,
        array $options = []
    ): self {
        return new self(
            recipient: $recipient,
            content: $content,
            channel: 'sms',
            options: $options
        );
    }

    /**
     * Create a WhatsApp message
     */
    public static function whatsapp(
        string $recipient,
        string $content,
        array $options = []
    ): self {
        return new self(
            recipient: $recipient,
            content: $content,
            channel: 'whatsapp',
            options: $options
        );
    }

    /**
     * Create message with template
     */
    public static function withTemplate(
        string $recipient,
        string $channel,
        string $template,
        array $templateData = [],
        array $options = []
    ): self {
        return new self(
            recipient: $recipient,
            content: '',
            channel: $channel,
            template: $template,
            templateData: $templateData,
            options: $options
        );
    }

    /**
     * Add attachments to the message
     */
    public function withAttachments(array $attachments): self
    {
        return new self(
            recipient: $this->recipient,
            content: $this->content,
            channel: $this->channel,
            options: $this->options,
            subject: $this->subject,
            attachments: $attachments,
            template: $this->template,
            templateData: $this->templateData,
            scheduledAt: $this->scheduledAt,
            priority: $this->priority,
            metadata: $this->metadata
        );
    }

    /**
     * Schedule the message for later delivery
     */
    public function scheduleAt(string $scheduledAt): self
    {
        return new self(
            recipient: $this->recipient,
            content: $this->content,
            channel: $this->channel,
            options: $this->options,
            subject: $this->subject,
            attachments: $this->attachments,
            template: $this->template,
            templateData: $this->templateData,
            scheduledAt: $scheduledAt,
            priority: $this->priority,
            metadata: $this->metadata
        );
    }

    /**
     * Set message priority
     */
    public function withPriority(int $priority): self
    {
        return new self(
            recipient: $this->recipient,
            content: $this->content,
            channel: $this->channel,
            options: $this->options,
            subject: $this->subject,
            attachments: $this->attachments,
            template: $this->template,
            templateData: $this->templateData,
            scheduledAt: $this->scheduledAt,
            priority: $priority,
            metadata: $this->metadata
        );
    }

    /**
     * Add metadata to the message
     */
    public function withMetadata(array $metadata): self
    {
        return new self(
            recipient: $this->recipient,
            content: $this->content,
            channel: $this->channel,
            options: $this->options,
            subject: $this->subject,
            attachments: $this->attachments,
            template: $this->template,
            templateData: $this->templateData,
            scheduledAt: $this->scheduledAt,
            priority: $this->priority,
            metadata: array_merge($this->metadata ?? [], $metadata)
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'recipient' => $this->recipient,
            'content' => $this->content,
            'channel' => $this->channel,
            'options' => $this->options,
            'subject' => $this->subject,
            'attachments' => $this->attachments,
            'template' => $this->template,
            'template_data' => $this->templateData,
            'scheduled_at' => $this->scheduledAt,
            'priority' => $this->priority,
            'metadata' => $this->metadata,
        ];
    }
}
