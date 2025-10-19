<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SMSDeliveryReport;

class SMSWebhookController extends Controller
{
    public function handleDeliveryReport(Request $request)
    {
        try {
            // Log the incoming webhook data
            Log::info('SMS Delivery Report Webhook Received', [
                'payload' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            // Validate required fields
            $messageId = $request->input('id');
            $status = $request->input('status');
            $phoneNumber = $request->input('phoneNumber');

            if (!$messageId || !$status || !$phoneNumber) {
                Log::warning('Invalid webhook payload - missing required fields', [
                    'payload' => $request->all()
                ]);
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            // Store delivery report
            SMSDeliveryReport::updateOrCreate(
                ['message_id' => $messageId],
                [
                    'phone_number' => $phoneNumber,
                    'status' => $status,
                    'network_code' => $request->input('networkCode'),
                    'failure_reason' => $request->input('failureReason'),
                    'retry_count' => $request->input('retryCount', 0),
                    'delivered_at' => now(),
                    'webhook_payload' => $request->all()
                ]
            );

            // Update your communication history if you have one
            $this->updateCommunicationHistory($messageId, $status, $request->all());

            Log::info('SMS Delivery Report Processed', [
                'message_id' => $messageId,
                'status' => $status,
                'phone_number' => $phoneNumber
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error processing SMS delivery report', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function updateCommunicationHistory($messageId, $status, $payload)
    {
        try {
            // Update your communication log/history table if you have one
            // This is optional - implement based on your data structure

            // Example:
            // CommunicationLog::where('external_message_id', $messageId)
            //     ->update([
            //         'delivery_status' => $status,
            //         'delivery_updated_at' => now(),
            //         'delivery_details' => $payload
            //     ]);

        } catch (\Exception $e) {
            Log::warning('Failed to update communication history', [
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
