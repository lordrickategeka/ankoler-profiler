<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Communication\CommunicationManager;
use App\Contracts\Communication\CommunicationMessage;

class TestCommunicationManagerCommand extends Command
{
    protected $signature = 'communication:test-manager';
    protected $description = 'Test the CommunicationManager with SMS';

    public function handle()
    {
        $this->info('🔍 Testing CommunicationManager SMS Integration');
        $this->line('============================================');
        
        try {
            $manager = app(CommunicationManager::class);
            
            // Test 1: Check if SMS channel is available
            $this->info('📋 Test 1: Channel Availability');
            $isSmsAvailable = $manager->isChannelAvailable('sms');
            $this->line("SMS Channel Available: " . ($isSmsAvailable ? 'Yes' : 'No'));
            
            if (!$isSmsAvailable) {
                $this->error('❌ SMS channel is not available. Please check configuration.');
                return 1;
            }
            
            // Test 2: Get available channels
            $this->info('📋 Test 2: Available Channels');
            $channels = $manager->getAvailableChannels();
            foreach ($channels as $name => $info) {
                $this->line("  {$name}: {$info['display_name']} (Max: {$info['max_length']} chars)");
            }
            
            // Test 3: Send SMS via CommunicationManager
            $this->info('📱 Test 3: Send SMS via CommunicationManager');
            
            $message = CommunicationMessage::sms(
                recipient: '+256760081801',
                content: 'Test from CommunicationManager at ' . now()->format('H:i:s'),
                options: []
            );
            
            $result = $manager->send($message);
            
            if ($result->isSuccessful()) {
                $this->info('✅ SMS sent successfully via CommunicationManager');
                $this->line('Message ID: ' . $result->messageId);
                $this->line('Provider Message ID: ' . ($result->providerMessageId ?? 'N/A'));
                if ($result->metadata) {
                    $this->line('Cost: ' . ($result->metadata['cost'] ?? 'N/A'));
                }
            } else {
                $this->error('❌ SMS sending failed');
                $this->line('Error: ' . $result->errorMessage);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Test failed');
            $this->line('Error: ' . $e->getMessage());
            $this->line('File: ' . $e->getFile() . ':' . $e->getLine());
        }
        
        return 0;
    }
}