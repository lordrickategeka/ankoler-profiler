<?php

namespace App\Providers;

use App\Services\AfricasTalkingSmsService;
use App\Services\DirectAfricasTalkingSmsService;
use App\Services\Communication\CommunicationManager;
use App\Services\Communication\EmailCommunicationService;
use App\Services\Communication\SMSCommunicationService;
use App\Services\Communication\SmsChannel;
use App\Services\Communication\WhatsAppCommunicationService;
use App\Services\CommunicationChannelManager;
use Illuminate\Support\ServiceProvider;

class CommunicationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Direct Africa's Talking SMS Service (bypasses SDK issues)
        $this->app->singleton(DirectAfricasTalkingSmsService::class, function () {
            return new DirectAfricasTalkingSmsService();
        });

        // Keep the old service for backward compatibility if needed
        $this->app->singleton(AfricasTalkingSmsService::class, function () {
            return new AfricasTalkingSmsService();
        });

        // Register SMS Channel with Direct service
        $this->app->singleton(SmsChannel::class, function ($app) {
            return new SmsChannel($app->make(DirectAfricasTalkingSmsService::class));
        });

        // Register individual communication services
        $this->app->singleton(EmailCommunicationService::class);
        
        // Register SMS Communication Service with Direct SMS dependency
        $this->app->singleton(SMSCommunicationService::class, function ($app) {
            return new SMSCommunicationService($app->make(DirectAfricasTalkingSmsService::class));
        });
        
        $this->app->singleton(WhatsAppCommunicationService::class);

        // Register the communication channel manager
        $this->app->singleton(CommunicationChannelManager::class, function ($app) {
            $manager = new CommunicationChannelManager();
            
            // Register available channels
            $manager->registerChannel('sms', $app->make(SmsChannel::class));
            
            return $manager;
        });

        // Register the legacy communication manager
        $this->app->singleton(CommunicationManager::class, function ($app) {
            return new CommunicationManager(
                $app->make(EmailCommunicationService::class),
                $app->make(SMSCommunicationService::class),
                $app->make(WhatsAppCommunicationService::class)
            );
        });

        // Create aliases for easier access
        $this->app->alias(CommunicationManager::class, 'communication');
        $this->app->alias(CommunicationChannelManager::class, 'communication.channels');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/africastalking.php' => config_path('africastalking.php'),
            ], 'africastalking-config');
        }
    }
}
