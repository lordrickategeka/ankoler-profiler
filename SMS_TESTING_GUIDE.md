# Africa's Talking SMS Testing Guide

This guide provides comprehensive testing tools for the Africa's Talking SMS integration in your Laravel application.

## 🔧 Setup

Make sure your `.env` file contains the Africa's Talking credentials:

```env
AT_USERNAME=sandbox
AT_API_KEY=your_api_key_here
AT_ENVIRONMENT=sandbox
AT_SENDER_ID=SHORTCODE
AT_DELIVERY_REPORTS=true
AT_LOGGING=true
```

## 🧪 Testing Methods

### 1. Feature Tests (Comprehensive)

Run the full test suite:
```bash
php artisan test --filter AfricasTalkingSmsTest
```

This will run comprehensive tests including:
- Configuration validation
- Connection testing
- Phone number validation
- Message length validation
- Error handling
- Sandbox SMS sending (if in sandbox mode)

### 2. Debug Command (Interactive)

Run the interactive debug command:
```bash
php artisan sms:debug
```

With options:
```bash
# Test with specific phone number
php artisan sms:debug --test-phone=+256760081801

# Include actual SMS sending test
php artisan sms:debug --send-test
```

This command provides:
- ✅ Configuration check
- 🔌 API connection test
- 💰 Account balance check
- 📱 Phone validation tests
- 🔗 Channel manager verification
- 📤 Optional SMS sending test

### 3. Tinker Testing (Manual)

#### Option A: Run the test script
```bash
php artisan tinker
```

Then in tinker:
```php
include 'sms_test_script.php';
```

#### Option B: Manual testing commands

Start tinker:
```bash
php artisan tinker
```

Then run these commands one by one:

```php
// Get SMS service
$smsService = app(\App\Services\AfricasTalkingSmsService::class);

// Test 1: Check configuration
dump(config('africastalking'));

// Test 2: Get debug info
dump($smsService->getDebugInfo());

// Test 3: Test connection
dump($smsService->testConnection());

// Test 4: Check account balance
dump($smsService->getAccountBalance());

// Test 5: Test phone number formatting
dump($smsService->formatPhoneNumber('0760081801'));
dump($smsService->formatPhoneNumber('+256760081801'));

// Test 6: Validate phone numbers
dump($smsService->validatePhoneNumber('+256760081801'));
dump($smsService->validatePhoneNumber('invalid-phone'));

// Test 7: Send test SMS (sandbox only)
$result = $smsService->sendSms('+254711XXXYYY', 'Test message from Tinker');
dump($result->toArray());
```

### 4. Channel Manager Testing

Test the communication channel system:

```php
// Get channel manager
$channelManager = app(\App\Services\CommunicationChannelManager::class);

// Check SMS channel availability
$channelManager->isChannelAvailable('sms');

// Get channel information
dump($channelManager->getChannelInfo());

// Test bulk SMS (sandbox)
$results = $channelManager->sendBulk('sms', ['+254711XXXYYY'], 'Bulk test message');
dump($results->toArray());
```

### 5. Template Integration Testing

Test SMS with communication templates:

```php
// Get a SMS template
$template = \App\Models\CommunicationTemplate::whereJsonContains('supported_channels', 'sms')->first();

// Get template service
$templateService = app(\App\Services\EmailTemplateService::class);

// Preview template
$preview = $templateService->previewTemplate($template);
dump($preview);
```

## 🐛 Common Issues & Solutions

### Issue: "Unauthorized" Error
**Solution:** Check your `AT_API_KEY` and `AT_USERNAME` in `.env`

### Issue: "Invalid phone number format"
**Solution:** Use international format with country code (+256, +254, etc.)

### Issue: "Message exceeds maximum length"
**Solution:** Keep SMS messages under 160 characters

### Issue: Connection timeouts
**Solution:** Check your internet connection and API credentials

### Issue: "Channel not available"
**Solution:** Run `php artisan cache:clear` and check configuration

## 📊 Test Results Interpretation

### Connection Test Results
- ✅ `success: true` - API credentials are correct
- ❌ `success: false` - Check credentials or network connection

### Account Balance Results
- ✅ Shows balance and currency - Account is active
- ❌ Error message - Check API permissions

### SMS Sending Results
- ✅ `isSuccessful(): true` - SMS request accepted
- ❌ `isSuccessful(): false` - Check phone number and message

### Phone Validation Results
- Valid formats: `+256760081801`, `+254711123456`, `+255621234567`
- Invalid formats: `invalid`, `123`, `abcdef`

## 🚀 Next Steps

1. **Run all tests** to ensure everything works
2. **Test with your actual phone number** in sandbox mode
3. **Check logs** in `storage/logs/laravel.log` for detailed information
4. **Switch to production** when ready by changing `AT_ENVIRONMENT=production`

## 📝 Logging

All SMS operations are logged when `AT_LOGGING=true`. Check the logs at:
```bash
tail -f storage/logs/laravel.log | grep "AfricasTalking"
```

## 💡 Tips

- Always test in **sandbox mode** first
- Use **international phone number formats** (+256...)
- Keep messages **under 160 characters** for standard SMS
- Monitor your **account balance** regularly
- Check **delivery reports** for important messages

---

Happy testing! 🎉