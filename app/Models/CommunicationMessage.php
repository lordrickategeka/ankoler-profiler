<?php

namespace App\Models;

use App\Contracts\Communication\CommunicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CommunicationMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'message_id',
        'provider_message_id',
        'sent_by_user_id',
        'organisation_id',
        'recipient_person_id',
        'recipient_identifier',
        'recipient_type',
        'channel',
        'subject',
        'content',
        'message_type',
        'template_data',
        'attachments',
        'status',
        'error_message',
        'delivery_details',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
        'metadata',
        'priority',
        'is_bulk_message',
        'bulk_message_id',
        'provider',
        'provider_response',
    ];

    protected $casts = [
        'template_data' => 'array',
        'attachments' => 'array',
        'delivery_details' => 'array',
        'metadata' => 'array',
        'provider_response' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
        'is_bulk_message' => 'boolean',
        'priority' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            if (empty($message->message_id)) {
                $message->message_id = $message->generateMessageId();
            }
        });
    }

    /**
     * Generate a unique message ID
     */
    public function generateMessageId(): string
    {
        return $this->channel . '_' . time() . '_' . Str::random(10);
    }

    /**
     * Get the communication status enum
     */
    public function getStatusEnum(): CommunicationStatus
    {
        return CommunicationStatus::from($this->status);
    }

    /**
     * Update the message status
     */
    public function updateStatus(CommunicationStatus $status, ?string $errorMessage = null): void
    {
        $this->status = $status->value;

        if ($errorMessage) {
            $this->error_message = $errorMessage;
        }

        // Set timestamp based on status
        match($status) {
            CommunicationStatus::SENT => $this->sent_at = now(),
            CommunicationStatus::DELIVERED => $this->delivered_at = now(),
            CommunicationStatus::READ => $this->read_at = now(),
            CommunicationStatus::FAILED => $this->failed_at = now(),
            default => null,
        };

        $this->save();
    }

    /**
     * Check if message is in a final state
     */
    public function isComplete(): bool
    {
        return $this->getStatusEnum()->isComplete();
    }

    /**
     * Check if message was successful
     */
    public function isSuccessful(): bool
    {
        return $this->getStatusEnum()->isSuccessful();
    }

    /**
     * Check if message failed
     */
    public function isFailed(): bool
    {
        return $this->getStatusEnum()->isFailed();
    }

    /**
     * Get the status color for UI
     */
    public function getStatusColor(): string
    {
        return $this->getStatusEnum()->getColor();
    }

    /**
     * Get formatted recipient display
     */
    public function getRecipientDisplayAttribute(): string
    {
        if ($this->recipientPerson) {
            return $this->recipientPerson->full_name . ' (' . $this->recipient_identifier . ')';
        }

        return $this->recipient_identifier;
    }

    /**
     * Get channel icon
     */
    public function getChannelIconAttribute(): string
    {
        return match($this->channel) {
            'email' => '📧',
            'sms' => '📱',
            'whatsapp' => '💬',
            default => '📬',
        };
    }

    /**
     * Scope for filtering by channel
     */
    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by organisation
     */
    public function scopeByOrganisation($query, int $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    /**
     * Scope for bulk messages
     */
    public function scopeBulkMessages($query, string $bulkMessageId)
    {
        return $query->where('bulk_message_id', $bulkMessageId);
    }

    /**
     * Scope for scheduled messages
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '>', now())
                    ->where('status', 'pending');
    }

    /**
     * Scope for messages ready to send
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'pending')
                    ->where(function ($q) {
                        $q->whereNull('scheduled_at')
                          ->orWhere('scheduled_at', '<=', now());
                    });
    }

    /**
     * Relationships
     */
    public function sentByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function recipientPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'recipient_person_id');
    }

    /**
     * Get related bulk messages
     */
    public function bulkSiblings()
    {
        if (!$this->bulk_message_id) {
            return collect();
        }

        return static::where('bulk_message_id', $this->bulk_message_id)
                    ->where('id', '!=', $this->id)
                    ->get();
    }

    /**
     * Get delivery rate for bulk messages
     */
    public static function getBulkDeliveryRate(string $bulkMessageId): array
    {
        $messages = static::where('bulk_message_id', $bulkMessageId)->get();
        $total = $messages->count();

        if ($total === 0) {
            return ['total' => 0, 'sent' => 0, 'delivered' => 0, 'failed' => 0, 'pending' => 0];
        }

        $sent = $messages->where('status', 'sent')->count();
        $delivered = $messages->where('status', 'delivered')->count();
        $failed = $messages->whereIn('status', ['failed', 'bounced', 'rejected'])->count();
        $pending = $messages->where('status', 'pending')->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'delivered' => $delivered,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($sent + $delivered) / $total * 100, 2) : 0,
        ];
    }
}
