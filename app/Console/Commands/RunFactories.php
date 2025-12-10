<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunFactories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'factories:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Laravel factories to generate sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting factory data generation...');

        // Create Organizations
        $this->info('Creating Organizations...');
        \App\Models\Organization::factory()->count(10)->create();
        $this->info('✓ Created 10 organizations');

        // Create Users
        $this->info('Creating Users...');
        \App\Models\User::factory()->count(20)->create();
        $this->info('✓ Created 20 users');

        // Create Persons
        $this->info('Creating Persons...');
        \App\Models\Person::factory()->count(50)->create();
        $this->info('✓ Created 50 persons');

        // Create Phone Numbers
        $this->info('Creating Phone Numbers...');
        \App\Models\Phone::factory()->count(30)->create();
        $this->info('✓ Created 30 phone numbers');

        // Create Email Addresses
        $this->info('Creating Email Addresses...');
        \App\Models\EmailAddress::factory()->count(25)->create();
        $this->info('✓ Created 25 email addresses');

        $this->info('Factory data generation completed successfully!');

        // Show final counts
        $this->table(
            ['Model', 'Count'],
            [
                ['Organizations', \App\Models\Organization::count()],
                ['Users', \App\Models\User::count()],
                ['Persons', \App\Models\Person::count()],
                ['Phone Numbers', \App\Models\Phone::count()],
                ['Email Addresses', \App\Models\EmailAddress::count()],
            ]
        );
    }
}
