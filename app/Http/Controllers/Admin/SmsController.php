<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunicationTemplate;
use App\Models\Person;
use App\Services\AfricasTalkingSmsService;
use App\Services\CommunicationChannelManager;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    protected AfricasTalkingSmsService $smsService;
    protected CommunicationChannelManager $channelManager;
    protected EmailTemplateService $templateService;

    public function __construct(
        AfricasTalkingSmsService $smsService,
        CommunicationChannelManager $channelManager,
        EmailTemplateService $templateService
    ) {
        $this->smsService = $smsService;
        $this->channelManager = $channelManager;
        $this->templateService = $templateService;
    }

    /**
     * Show SMS management dashboard
     */
    public function index()
    {
        // Get SMS channel information
        $channelInfo = $this->channelManager->getChannelInfo()['sms'] ?? null;
        $isAvailable = $this->channelManager->isChannelAvailable('sms');
        
        // Get account balance
        $accountInfo = $this->smsService->getAccountBalance();
        
        // Get SMS templates
        $smsTemplates = CommunicationTemplate::active()
            ->whereJsonContains('supported_channels', 'sms')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Get recent SMS statistics (you might want to implement this)
        $recentStats = [
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0,
        ];

        return view('admin.sms.index', compact(
            'channelInfo',
            'isAvailable',
            'accountInfo',
            'smsTemplates',
            'recentStats'
        ));
    }

    /**
     * Show SMS compose form
     */
    public function compose()
    {
        $smsTemplates = CommunicationTemplate::active()
            ->whereJsonContains('supported_channels', 'sms')
            ->orderBy('name')
            ->get();

        return view('admin.sms.compose', compact('smsTemplates'));
    }

    /**
     * Send single SMS
     */
    public function sendSingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|string',
            'message' => 'required|string|max:160',
            'template_id' => 'nullable|exists:communication_templates,id',
            'person_id' => 'nullable|exists:persons,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Validate phone number
            if (!$this->smsService->validatePhoneNumber($request->recipient)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number format'
                ], 422);
            }

            $result = null;

            if ($request->template_id && $request->person_id) {
                // Send with template
                $template = CommunicationTemplate::findOrFail($request->template_id);
                $person = Person::findOrFail($request->person_id);

                $message = $this->templateService->createMessageFromTemplate(
                    template: $template,
                    person: $person,
                    channel: 'sms',
                    extraVariables: [
                        'current_datetime' => now()->format('F j, Y \a\t g:i A'),
                    ]
                );

                $result = $this->channelManager->send($message);
            } else {
                // Send direct message
                $result = $this->smsService->sendSms(
                    recipient: $request->recipient,
                    message: $request->message
                );
            }

            if ($result->isSuccessful()) {
                // Log successful SMS
                Log::info('SMS sent successfully', [
                    'recipient' => $result->recipient,
                    'message_id' => $result->messageId,
                    'provider_message_id' => $result->providerMessageId,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'data' => [
                        'message_id' => $result->messageId,
                        'provider_message_id' => $result->providerMessageId,
                        'cost' => $result->metadata['cost'] ?? null,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result->errorMessage
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'recipient' => $request->recipient,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|string',
            'message' => 'required|string|max:160',
            'template_id' => 'nullable|exists:communication_templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $recipients = $request->recipients;
            $successCount = 0;
            $failureCount = 0;
            $results = [];

            if ($request->template_id) {
                // Send with template to persons
                $template = CommunicationTemplate::findOrFail($request->template_id);
                
                // Assume recipients are person IDs when using template
                $persons = Person::whereIn('person_id', $recipients)->get();
                
                $bulkResults = $this->channelManager->sendPersonalized(
                    channelName: 'sms',
                    persons: $persons,
                    template: $template->content,
                    options: [
                        'current_datetime' => now()->format('F j, Y \a\t g:i A'),
                    ]
                );
            } else {
                // Send direct bulk SMS
                $bulkResults = $this->channelManager->sendBulk(
                    channelName: 'sms',
                    recipients: $recipients,
                    message: $request->message
                );
            }

            foreach ($bulkResults as $result) {
                if ($result->isSuccessful()) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
                
                $results[] = [
                    'recipient' => $result->recipient,
                    'success' => $result->isSuccessful(),
                    'message_id' => $result->messageId,
                    'error' => $result->errorMessage ?? null,
                ];
            }

            Log::info('Bulk SMS completed', [
                'total_recipients' => count($recipients),
                'successful' => $successCount,
                'failed' => $failureCount,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Bulk SMS completed: {$successCount} sent, {$failureCount} failed",
                'data' => [
                    'total' => count($recipients),
                    'successful' => $successCount,
                    'failed' => $failureCount,
                    'results' => $results,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk SMS failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bulk SMS failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get SMS templates for AJAX
     */
    public function getTemplates()
    {
        $templates = CommunicationTemplate::active()
            ->whereJsonContains('supported_channels', 'sms')
            ->select('id', 'name', 'description', 'content', 'variables')
            ->get();

        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }

    /**
     * Preview SMS template
     */
    public function previewTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:communication_templates,id',
            'person_id' => 'nullable|exists:persons,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $template = CommunicationTemplate::findOrFail($request->template_id);
            
            if ($request->person_id) {
                $person = Person::findOrFail($request->person_id);
            } else {
                $person = null;
            }

            $preview = $this->templateService->previewTemplate($template, $person);

            return response()->json([
                'success' => true,
                'preview' => [
                    'subject' => $preview['subject'] ?? null,
                    'content' => $preview['content'],
                    'length' => strlen($preview['content']),
                    'max_length' => 160,
                    'within_limit' => strlen($preview['content']) <= 160,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get account information
     */
    public function getAccountInfo()
    {
        try {
            $accountInfo = $this->smsService->getAccountBalance();
            $channelInfo = $this->channelManager->getChannelInfo()['sms'] ?? null;

            return response()->json([
                'success' => true,
                'account' => $accountInfo,
                'channel' => $channelInfo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve account info: ' . $e->getMessage()
            ], 500);
        }
    }
}