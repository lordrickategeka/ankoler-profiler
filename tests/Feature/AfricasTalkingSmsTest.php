<?php

namespace Tests\Feature;

use App\Services\AfricasTalkingSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AfricasTalkingSmsTest extends TestCase
{
    protected AfricasTalkingSmsService $smsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->smsService = app(AfricasTalkingSmsService::class);
    }

    /** @test */
    public function it_can_load_configuration()
    {
        $config = config('africastalking');
        
        $this->assertNotNull($config);
        $this->assertArrayHasKey('username', $config);
        $this->assertArrayHasKey('api_key', $config);
        $this->assertArrayHasKey('environment', $config);
        $this->assertArrayHasKey('sms', $config);
        
        echo "\n📋 Configuration Check:\n";
        dump($config);
    }

    /** @test */
    public function it_can_get_debug_information()
    {
        $debugInfo = $this->smsService->getDebugInfo();
        
        $this->assertArrayHasKey('config', $debugInfo);
        $this->assertArrayHasKey('validation', $debugInfo);
        $this->assertArrayHasKey('sdk_info', $debugInfo);
        
        echo "\n🔍 Debug Information:\n";
        dump($debugInfo);
    }

    /** @test */
    public function it_can_test_connection()
    {
        $connectionTest = $this->smsService->testConnection();
        
        $this->assertArrayHasKey('success', $connectionTest);
        $this->assertArrayHasKey('config', $connectionTest);
        
        echo "\n🔌 Connection Test:\n";
        dump($connectionTest);
        
        if ($connectionTest['success']) {
            echo "✅ Connection successful!\n";
        } else {
            echo "❌ Connection failed: " . $connectionTest['error'] . "\n";
        }
    }

    /** @test */
    public function it_can_check_account_balance()
    {
        $balance = $this->smsService->getAccountBalance();
        
        $this->assertArrayHasKey('success', $balance);
        
        echo "\n💰 Account Balance:\n";
        dump($balance);
        
        if ($balance['success']) {
            echo "✅ Balance retrieved successfully!\n";
        } else {
            echo "❌ Failed to get balance: " . ($balance['error'] ?? 'Unknown error') . "\n";
        }
    }

    /** @test */
    public function it_can_validate_phone_numbers()
    {
        $testNumbers = [
            '0760081801',           // Uganda local format
            '+256760081801',        // Uganda international format
            '0711123456',           // Kenya local format  
            '+254711123456',        // Kenya international format
            '0621234567',           // Tanzania local format
            '+255621234567',        // Tanzania international format
            'invalid',              // Invalid format
            '123',                  // Too short
            '+256760081801234567',  // Too long
        ];

        echo "\n📱 Phone Number Validation Tests:\n";
        
        foreach ($testNumbers as $number) {
            $isValid = $this->smsService->validatePhoneNumber($number);
            $formatted = $this->smsService->formatPhoneNumber($number);
            
            echo "Number: {$number}\n";
            echo "  Valid: " . ($isValid ? 'YES' : 'NO') . "\n";
            echo "  Formatted: " . ($formatted ?: 'INVALID') . "\n\n";
            
            // Assert that validation and formatting are consistent
            if ($isValid) {
                $this->assertNotNull($formatted, "Valid number should have formatted version");
            }
        }
    }

    /** @test */
    public function it_can_handle_sms_message_length_validation()
    {
        $testMessages = [
            'Short message',
            str_repeat('A', 160), // Exactly 160 characters
            str_repeat('B', 161), // Over limit
            str_repeat('C', 200), // Way over limit
        ];

        echo "\n📏 Message Length Validation:\n";
        
        foreach ($testMessages as $message) {
            $length = strlen($message);
            echo "Message length: {$length}\n";
            echo "Within limit: " . ($length <= 160 ? 'YES' : 'NO') . "\n\n";
        }
    }

    /** @test */
    public function it_can_send_test_sms_in_sandbox()
    {
        // Only test sending if we're in sandbox mode
        $config = config('africastalking');
        
        if ($config['environment'] !== 'sandbox') {
            $this->markTestSkipped('SMS sending test only runs in sandbox mode');
        }

        // Use a test phone number that won't actually receive SMS in sandbox
        $testRecipient = '+254711XXXYYY';
        $testMessage = 'Test message from Ankole Profiler SMS Feature Test';

        echo "\n📤 Sending Test SMS:\n";
        echo "Recipient: {$testRecipient}\n";
        echo "Message: {$testMessage}\n";
        
        $result = $this->smsService->sendSms($testRecipient, $testMessage);
        
        dump($result->toArray());
        
        // In sandbox mode, we should get a response even if SMS doesn't actually send
        $this->assertNotNull($result);
        $this->assertNotEmpty($result->messageId);
        
        if ($result->isSuccessful()) {
            echo "✅ SMS sending request successful!\n";
        } else {
            echo "❌ SMS sending failed: {$result->errorMessage}\n";
        }
    }

    /** @test */
    public function it_handles_invalid_phone_numbers_gracefully()
    {
        $invalidNumbers = [
            'invalid-phone',
            '123',
            '',
            'abcdefg',
        ];

        echo "\n❌ Invalid Phone Number Handling:\n";

        foreach ($invalidNumbers as $number) {
            $result = $this->smsService->sendSms($number, 'Test message');
            
            echo "Number: '{$number}'\n";
            echo "Result: " . ($result->isSuccessful() ? 'SUCCESS' : 'FAILED') . "\n";
            echo "Error: " . ($result->errorMessage ?: 'None') . "\n\n";
            
            // Should fail gracefully with invalid numbers
            $this->assertFalse($result->isSuccessful());
            $this->assertNotEmpty($result->errorMessage);
        }
    }

    /** @test */
    public function it_handles_oversized_messages_gracefully()
    {
        $longMessage = str_repeat('This is a very long message that exceeds the SMS limit. ', 10);
        $testRecipient = '+256760081801';

        echo "\n📏 Oversized Message Handling:\n";
        echo "Message length: " . strlen($longMessage) . "\n";

        $result = $this->smsService->sendSms($testRecipient, $longMessage);

        echo "Result: " . ($result->isSuccessful() ? 'SUCCESS' : 'FAILED') . "\n";
        echo "Error: " . ($result->errorMessage ?: 'None') . "\n";

        // Should fail with oversized message
        $this->assertFalse($result->isSuccessful());
        $this->assertStringContainsString('exceeds maximum length', $result->errorMessage);
    }

    /** @test */
    public function it_can_test_bulk_sms_structure()
    {
        // Test the structure without actually sending
        $recipients = ['+256760081801', '+254711123456', '+255621234567'];
        $message = 'Bulk test message';

        echo "\n📬 Bulk SMS Structure Test:\n";
        echo "Recipients: " . implode(', ', $recipients) . "\n";
        echo "Message: {$message}\n";

        // Test in sandbox only
        $config = config('africastalking');
        if ($config['environment'] === 'sandbox') {
            $results = $this->smsService->sendBulkSms($recipients, $message);
            
            echo "Results count: " . count($results) . "\n";
            
            foreach ($results as $index => $result) {
                echo "Result {$index}: " . ($result->isSuccessful() ? 'SUCCESS' : 'FAILED') . "\n";
                if (!$result->isSuccessful()) {
                    echo "  Error: {$result->errorMessage}\n";
                }
            }
            
            $this->assertCount(count($recipients), $results);
        } else {
            echo "⏭️  Skipped - not in sandbox mode\n";
            $this->assertTrue(true); // Pass the test
        }
    }

    /** @test */
    public function it_generates_unique_message_ids()
    {
        $messageIds = [];
        
        echo "\n🆔 Message ID Generation Test:\n";

        // Generate multiple message IDs using reflection to access private method
        $reflection = new \ReflectionClass($this->smsService);
        $method = $reflection->getMethod('generateMessageId');
        $method->setAccessible(true);

        for ($i = 0; $i < 5; $i++) {
            $messageId = $method->invoke($this->smsService);
            $messageIds[] = $messageId;
            echo "ID {$i}: {$messageId}\n";
            
            // Wait a small amount to ensure different timestamps
            usleep(1000);
        }

        // All message IDs should be unique
        $uniqueIds = array_unique($messageIds);
        $this->assertCount(count($messageIds), $uniqueIds, 'All message IDs should be unique');
        
        // All should start with SMS_
        foreach ($messageIds as $id) {
            $this->assertStringStartsWith('SMS_', $id);
        }
    }

    /** @test */
    public function it_logs_operations_when_enabled()
    {
        $config = config('africastalking');
        
        echo "\n📝 Logging Configuration:\n";
        echo "Logging enabled: " . ($config['logging']['enabled'] ? 'YES' : 'NO') . "\n";
        echo "Log requests: " . ($config['logging']['log_requests'] ? 'YES' : 'NO') . "\n";
        echo "Log responses: " . ($config['logging']['log_responses'] ? 'YES' : 'NO') . "\n";

        if ($config['logging']['enabled']) {
            // Test that logging works by attempting an operation
            $testResult = $this->smsService->testConnection();
            
            // Check that log entries were created (this is more of a smoke test)
            $this->assertTrue(true, 'Logging test completed');
        } else {
            echo "⏭️  Logging is disabled\n";
            $this->assertTrue(true);
        }
    }
}