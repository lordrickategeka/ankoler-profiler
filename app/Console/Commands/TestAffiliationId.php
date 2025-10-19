<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PersonAffiliation;

class TestAffiliationId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:affiliation-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test affiliation ID generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Affiliation ID Generation');
        
        // Get all current IDs
        $affiliations = PersonAffiliation::where('affiliation_id', 'like', 'AFF-%')->get();
        $this->info('Current affiliations count: ' . $affiliations->count());
        
        if ($affiliations->count() > 0) {
            $this->info('Current affiliation IDs:');
            foreach ($affiliations as $affiliation) {
                $this->line('- ' . $affiliation->affiliation_id . ' (Person: ' . $affiliation->person_id . ')');
            }
            
            // Find the highest numeric ID
            $maxNumeric = $affiliations->map(function($a) { 
                return (int) substr($a->affiliation_id, 4); 
            })->max();
            $this->info('Highest numeric ID: ' . $maxNumeric);
        }
        
        // Test new ID generation
        $this->info('Testing new ID generation:');
        for ($i = 1; $i <= 3; $i++) {
            $newId = PersonAffiliation::generateAffiliationId();
            $this->line('Generated ID ' . $i . ': ' . $newId);
        }
        
        // Check for duplicates
        $duplicates = PersonAffiliation::selectRaw('affiliation_id, COUNT(*) as count')
            ->groupBy('affiliation_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        if ($duplicates->count() > 0) {
            $this->error('Found duplicate affiliation IDs:');
            foreach ($duplicates as $dup) {
                $this->line('- ' . $dup->affiliation_id . ' appears ' . $dup->count . ' times');
            }
        } else {
            $this->info('No duplicate affiliation IDs found');
        }
        
        // Test actual creation
        $this->info('Testing actual affiliation creation...');
        try {
            // Find an existing person and organization
            $person = \App\Models\Person::first();
            $organisation = \App\Models\Organisation::first();
            
            if (!$person || !$organisation) {
                $this->warn('Skipping creation test - no person or organisation found');
                return;
            }
            
            $testAffiliation = new PersonAffiliation();
            $testAffiliation->person_id = $person->id;
            $testAffiliation->organisation_id = $organisation->id;
            $testAffiliation->role_type = 'TEST_ROLE';
            $testAffiliation->start_date = now();
            $testAffiliation->status = 'active';
            $testAffiliation->created_by = 1;
            
            $this->line('About to save affiliation with auto-generated ID...');
            $testAffiliation->save();
            
            $this->info('SUCCESS! Created test affiliation with ID: ' . $testAffiliation->affiliation_id);
            
            // Clean up - delete the test affiliation
            $testAffiliation->delete();
            $this->line('Test affiliation cleaned up.');
            
        } catch (\Exception $e) {
            $this->error('FAILED to create test affiliation: ' . $e->getMessage());
        }
    }
}
