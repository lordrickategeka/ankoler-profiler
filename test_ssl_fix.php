<?php

/*
|--------------------------------------------------------------------------
| SSL Fix Test Script for Africa's Talking SMS
|--------------------------------------------------------------------------
|
| This script helps test the SSL verification bypass for development.
| Run in tinker: php artisan tinker
| Then: include 'test_ssl_fix.php';
|
*/

echo "🔧 Testing SSL Fix for Africa's Talking SMS\n";
echo "==========================================\n\n";

// Check current environment
$environment = app()->environment();
echo "Current Environment: {$environment}\n";

// Get current configuration
$config = config('africastalking');
echo "SSL Verification Disabled: " . ($config['disable_ssl_verification'] ? 'YES' : 'NO') . "\n";
echo "Username: " . ($config['username'] ?: 'NOT SET') . "\n";
echo "Has API Key: " . (!empty($config['api_key']) ? 'YES' : 'NO') . "\n\n";

if ($environment !== 'local') {
    echo "⚠️  WARNING: This SSL fix should only be used in local development!\n";
    echo "Current environment is: {$environment}\n\n";
}

// Test the SMS service
echo "🧪 Testing SMS Service...\n";
echo "========================\n";

try {
    $smsService = app(\App\Services\AfricasTalkingSmsService::class);
    
    // Get debug info
    echo "Debug Information:\n";
    $debugInfo = $smsService->getDebugInfo();
    foreach ($debugInfo['config'] as $key => $value) {
        echo "  {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
    }
    echo "\n";
    
    // Test connection
    echo "Testing API Connection...\n";
    $connectionTest = $smsService->testConnection();
    
    if ($connectionTest['success']) {
        echo "✅ Connection successful!\n";
        if (isset($connectionTest['response']['UserData'])) {
            echo "  User ID: " . ($connectionTest['response']['UserData']['id'] ?? 'Unknown') . "\n";
        }
    } else {
        echo "❌ Connection failed!\n";
        echo "  Error: " . $connectionTest['error'] . "\n";
        
        if (!empty($connectionTest['suggestions'])) {
            echo "\n💡 Suggestions:\n";
            foreach ($connectionTest['suggestions'] as $suggestion) {
                echo "  • {$suggestion}\n";
            }
        }
    }
    
    echo "\n";
    
    // Test account balance
    echo "Testing Account Balance...\n";
    $balance = $smsService->getAccountBalance();
    
    if ($balance['success']) {
        echo "✅ Balance retrieved: " . $balance['balance'] . " " . $balance['currency'] . "\n";
    } else {
        echo "❌ Failed to get balance: " . ($balance['error'] ?? 'Unknown error') . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Critical error: " . $e->getMessage() . "\n";
    
    if (str_contains($e->getMessage(), 'SSL') || str_contains($e->getMessage(), 'certificate')) {
        echo "\n💡 SSL Error Detected!\n";
        echo "Try adding this to your .env file:\n";
        echo "AT_DISABLE_SSL_VERIFICATION=true\n\n";
        echo "Then run: php artisan config:clear\n";
    }
}

echo "\n🔍 Troubleshooting Steps:\n";
echo "========================\n";
echo "1. If you see SSL/certificate errors:\n";
echo "   • Add AT_DISABLE_SSL_VERIFICATION=true to .env\n";
echo "   • Run: php artisan config:clear\n";
echo "   • Restart your development server\n\n";

echo "2. If you see 'Unauthorized' errors:\n";
echo "   • Check AT_USERNAME and AT_API_KEY in .env\n";
echo "   • Make sure credentials are for the right environment\n\n";

echo "3. If you see connection timeout errors:\n";
echo "   • Check your internet connection\n";
echo "   • Check firewall/antivirus settings\n\n";

echo "4. After fixing issues:\n";
echo "   • Test again with: include 'test_ssl_fix.php';\n";
echo "   • Or run: php artisan sms:debug --send-test\n\n";

echo "⚠️  IMPORTANT: Remove AT_DISABLE_SSL_VERIFICATION=true before deploying to production!\n\n";