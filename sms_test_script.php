<?php

/*
|--------------------------------------------------------------------------
| Africa's Talking SMS Test Script for Laravel Tinker
|--------------------------------------------------------------------------
|
| Run this script in tinker with: php artisan tinker
| Then copy and paste these commands one by one, or include this file:
| include 'sms_test_script.php';
|
*/

echo "🚀 Starting Africa's Talking SMS Tests...\n\n";

// Get the SMS service instance
$smsService = app(\App\Services\AfricasTalkingSmsService::class);

echo "📋 Test 1: Configuration Check\n";
echo "=====================================\n";
$config = config('africastalking');
dump($config);
echo "\n";

echo "🔍 Test 2: Debug Information\n";
echo "=====================================\n";
$debugInfo = $smsService->getDebugInfo();
dump($debugInfo);
echo "\n";

echo "🔌 Test 3: Connection Test\n";
echo "=====================================\n";
$connectionTest = $smsService->testConnection();
dump($connectionTest);

if ($connectionTest['success']) {
    echo "✅ Connection successful!\n\n";
} else {
    echo "❌ Connection failed!\n";
    echo "Error: " . $connectionTest['error'] . "\n\n";
}

echo "💰 Test 4: Account Balance\n";
echo "=====================================\n";
$balance = $smsService->getAccountBalance();
dump($balance);

if ($balance['success']) {
    echo "✅ Balance: " . $balance['balance'] . " " . $balance['currency'] . "\n\n";
} else {
    echo "❌ Failed to get balance: " . ($balance['error'] ?? 'Unknown error') . "\n\n";
}

echo "📱 Test 5: Phone Number Formatting\n";
echo "=====================================\n";
$testNumbers = [
    '0760081801',           // Uganda local
    '+256760081801',        // Uganda international
    '0711123456',           // Kenya local
    '+254711123456',        // Kenya international
    'invalid-number',       // Invalid
];

foreach ($testNumbers as $number) {
    echo "Original: {$number}\n";
    echo "Valid: " . ($smsService->validatePhoneNumber($number) ? 'YES' : 'NO') . "\n";
    echo "Formatted: " . ($smsService->formatPhoneNumber($number) ?: 'INVALID') . "\n";
    echo "---\n";
}
echo "\n";

echo "📤 Test 6: Send Test SMS (Sandbox Only)\n";
echo "=====================================\n";

// Check if we're in sandbox mode
$environment = config('africastalking.environment');
echo "Environment: {$environment}\n";

if ($environment === 'sandbox') {
    // Use a test phone number - won't actually deliver in sandbox
    $testRecipient = '+254711XXXYYY';
    $testMessage = 'Test SMS from Ankole Profiler via Tinker';
    
    echo "Sending SMS to: {$testRecipient}\n";
    echo "Message: {$testMessage}\n";
    
    $result = $smsService->sendSms($testRecipient, $testMessage);
    
    echo "Result:\n";
    dump($result->toArray());
    
    if ($result->isSuccessful()) {
        echo "✅ SMS request successful!\n";
        echo "Message ID: {$result->messageId}\n";
        if ($result->providerMessageId) {
            echo "Provider Message ID: {$result->providerMessageId}\n";
        }
    } else {
        echo "❌ SMS request failed!\n";
        echo "Error: {$result->errorMessage}\n";
    }
} else {
    echo "⚠️  Skipping SMS test - not in sandbox mode\n";
    echo "Change AT_ENVIRONMENT to 'sandbox' in .env to test SMS sending\n";
}

echo "\n";

echo "🧪 Test 7: Error Handling Tests\n";
echo "=====================================\n";

// Test invalid phone number
echo "Testing invalid phone number...\n";
$invalidResult = $smsService->sendSms('invalid-phone', 'Test message');
echo "Invalid phone result: " . ($invalidResult->isSuccessful() ? 'SUCCESS' : 'FAILED') . "\n";
echo "Error message: " . ($invalidResult->errorMessage ?: 'None') . "\n";

// Test oversized message
echo "\nTesting oversized message...\n";
$longMessage = str_repeat('This message is too long! ', 20);
echo "Message length: " . strlen($longMessage) . " characters\n";
$oversizeResult = $smsService->sendSms('+256760081801', $longMessage);
echo "Oversize result: " . ($oversizeResult->isSuccessful() ? 'SUCCESS' : 'FAILED') . "\n";
echo "Error message: " . ($oversizeResult->errorMessage ?: 'None') . "\n";

echo "\n🎉 All tests completed!\n";

// Additional interactive commands you can run manually:
echo "\n📝 Additional Commands to Try:\n";
echo "=====================================\n";
echo "// Get channel manager:\n";
echo "\$channelManager = app(\\App\\Services\\CommunicationChannelManager::class);\n";
echo "\n// Check SMS channel availability:\n";
echo "\$channelManager->isChannelAvailable('sms');\n";
echo "\n// Get channel info:\n";
echo "\$channelManager->getChannelInfo();\n";
echo "\n// Test with communication templates:\n";
echo "\$template = \\App\\Models\\CommunicationTemplate::whereJsonContains('supported_channels', 'sms')->first();\n";
echo "\n// Test email template service:\n";
echo "\$templateService = app(\\App\\Services\\EmailTemplateService::class);\n";
echo "\n";