<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class AfricasTalkingCallbackController extends Controller
{
    /**
     * Handle Africa's Talking callback (delivery reports, incoming messages, etc.)
     */
    public function handle(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('AfricasTalking Callback Received', [
            'payload' => $request->all(),
            'raw' => $request->getContent(),
        ]);

        // Example: Handle delivery report
        if ($request->has('status')) {
            // Process delivery report
            // You can store status, messageId, etc. in your DB
            // $status = $request->input('status');
            // $messageId = $request->input('messageId');
        }

        // Example: Handle incoming message
        if ($request->has('text')) {
            // Process incoming SMS
            // $from = $request->input('from');
            // $text = $request->input('text');
        }

        // Always respond with 200 OK
        return response('Callback received', 200);
    }
}
