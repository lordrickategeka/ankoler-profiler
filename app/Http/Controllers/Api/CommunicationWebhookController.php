<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunicationMessage;
use App\Contracts\Communication\CommunicationStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CommunicationWebhookController extends Controller
{
    /**
     * Handle Twilio webhooks for SMS and WhatsApp
     */
    public function twilio(Request $request)
    {
        try {
            $messageId = $request->input('MessageSid');
            $status = $request->input('MessageStatus');
            $errorCode = $request->input('ErrorCode');
            $errorMessage = $request->input('ErrorMessage');

            Log::info('Twilio webhook received', $request->all());

            // Find the message by provider message ID
            $message = CommunicationMessage::where('provider_message_id', $messageId)->first();

            if (!$message) {
                Log::warning('Twilio webhook: Message not found', ['message_id' => $messageId]);
                return response()->json(['status' => 'message not found'], 404);
            }

            // Map Twilio status to our status
            $communicationStatus = $this->mapTwilioStatus($status);

            // Update message status
            if ($communicationStatus) {
                $message->updateStatus($communicationStatus, $errorMessage);

                Log::info('Message status updated via Twilio webhook', [
                    'message_id' => $message->message_id,
                    'twilio_status' => $status,
                    'our_status' => $communicationStatus->value,
                ]);
            }

            return response()->json(['status' => 'success']);

        } catch (Exception $e) {
            Log::error('Twilio webhook error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json(['error' => 'webhook processing failed'], 500);
        }
    }

    /**
     * Handle Africa's Talking webhooks for SMS
     */
    public function africasTalking(Request $request)
    {
        try {
            $messageId = $request->input('id');
            $status = $request->input('status');
            $failureReason = $request->input('failureReason');

            Log::info('Africa\'s Talking webhook received', $request->all());

            // Find the message by provider message ID
            $message = CommunicationMessage::where('provider_message_id', $messageId)->first();

            if (!$message) {
                Log::warning('Africa\'s Talking webhook: Message not found', ['message_id' => $messageId]);
                return response()->json(['status' => 'message not found'], 404);
            }

            // Map Africa's Talking status to our status
            $communicationStatus = $this->mapAfricasTalkingStatus($status);

            // Update message status
            if ($communicationStatus) {
                $message->updateStatus($communicationStatus, $failureReason);

                Log::info('Message status updated via Africa\'s Talking webhook', [
                    'message_id' => $message->message_id,
                    'at_status' => $status,
                    'our_status' => $communicationStatus->value,
                ]);
            }

            return response()->json(['status' => 'success']);

        } catch (Exception $e) {
            Log::error('Africa\'s Talking webhook error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json(['error' => 'webhook processing failed'], 500);
        }
    }

    /**
     * Handle Meta WhatsApp webhooks
     */
    public function metaWhatsApp(Request $request)
    {
        try {
            // Handle webhook verification
            if ($request->has('hub_mode') && $request->input('hub_mode') === 'subscribe') {
                $verifyToken = config('services.whatsapp.providers.meta.verify_token');
                $hubVerifyToken = $request->input('hub_verify_token');

                if ($hubVerifyToken === $verifyToken) {
                    return response($request->input('hub_challenge'));
                } else {
                    return response('Forbidden', 403);
                }
            }

            Log::info('Meta WhatsApp webhook received', $request->all());

            $entry = $request->input('entry.0');
            $changes = $entry['changes'] ?? [];

            foreach ($changes as $change) {
                $value = $change['value'] ?? [];
                $statuses = $value['statuses'] ?? [];

                foreach ($statuses as $status) {
                    $messageId = $status['id'];
                    $statusValue = $status['status'];
                    $timestamp = $status['timestamp'] ?? null;

                    // Find the message by provider message ID
                    $message = CommunicationMessage::where('provider_message_id', $messageId)->first();

                    if (!$message) {
                        Log::warning('Meta WhatsApp webhook: Message not found', ['message_id' => $messageId]);
                        continue;
                    }

                    // Map Meta status to our status
                    $communicationStatus = $this->mapMetaWhatsAppStatus($statusValue);

                    // Update message status
                    if ($communicationStatus) {
                        $message->updateStatus($communicationStatus);

                        Log::info('Message status updated via Meta WhatsApp webhook', [
                            'message_id' => $message->message_id,
                            'meta_status' => $statusValue,
                            'our_status' => $communicationStatus->value,
                        ]);
                    }
                }
            }

            return response()->json(['status' => 'success']);

        } catch (Exception $e) {
            Log::error('Meta WhatsApp webhook error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json(['error' => 'webhook processing failed'], 500);
        }
    }

    /**
     * Map Twilio status to our communication status
     */
    private function mapTwilioStatus(string $status): ?CommunicationStatus
    {
        return match ($status) {
            'queued', 'accepted' => CommunicationStatus::PENDING,
            'sending', 'sent' => CommunicationStatus::SENT,
            'delivered' => CommunicationStatus::DELIVERED,
            'read' => CommunicationStatus::READ,
            'failed', 'undelivered' => CommunicationStatus::FAILED,
            default => null,
        };
    }

    /**
     * Map Africa's Talking status to our communication status
     */
    private function mapAfricasTalkingStatus(string $status): ?CommunicationStatus
    {
        return match ($status) {
            'Buffered', 'Sent' => CommunicationStatus::SENT,
            'Success' => CommunicationStatus::DELIVERED,
            'Failed', 'Rejected' => CommunicationStatus::FAILED,
            default => null,
        };
    }

    /**
     * Map Meta WhatsApp status to our communication status
     */
    private function mapMetaWhatsAppStatus(string $status): ?CommunicationStatus
    {
        return match ($status) {
            'sent' => CommunicationStatus::SENT,
            'delivered' => CommunicationStatus::DELIVERED,
            'read' => CommunicationStatus::READ,
            'failed' => CommunicationStatus::FAILED,
            default => null,
        };
    }
}
