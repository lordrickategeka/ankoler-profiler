<?php

namespace App\Console\Commands;

use App\Models\PersonRelationship;
use App\Models\CrossOrgRelationship;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyRelationships extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'relationships:verify
                            {--type=all : Type to verify (all, personal, cross-org)}
                            {--confidence=0.8 : Minimum confidence for auto-verification}
                            {--interactive : Interactive verification mode}
                            {--auto : Auto-verify high confidence relationships}';

    /**
     * The console command description.
     */
    protected $description = 'Verify discovered relationships interactively or automatically';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $minConfidence = (float) $this->option('confidence');
        $interactive = $this->option('interactive');
        $auto = $this->option('auto');

        if ($auto && $interactive) {
            $this->error('Cannot use both --auto and --interactive options');
            return self::FAILURE;
        }

        if (!$auto && !$interactive) {
            $interactive = true; // Default to interactive
        }

        $this->info('Starting relationship verification...');

        try {
            if ($type === 'all' || $type === 'personal') {
                $this->verifyPersonalRelationships($minConfidence, $interactive, $auto);
            }

            if ($type === 'all' || $type === 'cross-org') {
                $this->verifyCrossOrgRelationships($minConfidence, $interactive, $auto);
            }

            $this->info('Verification completed successfully');
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Verification failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function verifyPersonalRelationships(float $minConfidence, bool $interactive, bool $auto): void
    {
        $relationships = PersonRelationship::query()
            ->where('verification_status', 'unverified')
            ->where('status', 'active')
            ->where('confidence_score', '>=', $minConfidence)
            ->with(['personA', 'personB'])
            ->orderByDesc('confidence_score')
            ->get();

        if ($relationships->isEmpty()) {
            $this->info('No personal relationships to verify');
            return;
        }

        $this->info("Found {$relationships->count()} personal relationships to verify");

        $verified = 0;
        $rejected = 0;

        foreach ($relationships as $relationship) {
            if ($auto) {
                // Auto-verify very high confidence relationships
                if ($relationship->confidence_score >= 0.95) {
                    $relationship->markAsVerified(1); // System user
                    $verified++;
                    continue;
                }
            }

            if ($interactive) {
                $action = $this->reviewPersonalRelationship($relationship);

                switch ($action) {
                    case 'verify':
                        $relationship->markAsVerified(1);
                        $verified++;
                        break;
                    case 'reject':
                        $relationship->markAsRejected(1);
                        $rejected++;
                        break;
                    case 'skip':
                        continue 2; // Continue to next relationship
                    case 'quit':
                        break 2; // Exit the loop
                }
            }
        }

        $this->info("Personal relationships - Verified: {$verified}, Rejected: {$rejected}");
    }

    private function verifyCrossOrgRelationships(float $minConfidence, bool $interactive, bool $auto): void
    {
        $relationships = CrossOrgRelationship::query()
            ->where('verified', false)
            ->where('status', 'active')
            ->where('impact_score', '>=', $minConfidence)
            ->with([
                'person',
                'primaryAffiliation.organisation',
                'secondaryAffiliation.organisation'
            ])
            ->orderByDesc('impact_score')
            ->get();

        if ($relationships->isEmpty()) {
            $this->info('No cross-org relationships to verify');
            return;
        }

        $this->info("Found {$relationships->count()} cross-org relationships to verify");

        $verified = 0;
        $rejected = 0;

        foreach ($relationships as $relationship) {
            if ($auto) {
                // Auto-verify very high impact relationships
                if ($relationship->impact_score >= 0.9) {
                    $relationship->markAsVerified(1);
                    $verified++;
                    continue;
                }
            }

            if ($interactive) {
                $action = $this->reviewCrossOrgRelationship($relationship);

                switch ($action) {
                    case 'verify':
                        $relationship->markAsVerified(1);
                        $verified++;
                        break;
                    case 'reject':
                        $relationship->update(['status' => 'inactive']);
                        $rejected++;
                        break;
                    case 'skip':
                        continue 2;
                    case 'quit':
                        break 2;
                }
            }
        }

        $this->info("Cross-org relationships - Verified: {$verified}, Rejected: {$rejected}");
    }

    private function reviewPersonalRelationship(PersonRelationship $relationship): string
    {
        $this->newLine();
        $this->line('=== PERSONAL RELATIONSHIP REVIEW ===');

        $personA = $relationship->personA;
        $personB = $relationship->personB;

        $this->table(['Field', 'Person A', 'Person B'], [
            ['Name', $personA->given_name . ' ' . $personA->family_name, $personB->given_name . ' ' . $personB->family_name],
            ['Date of Birth', $personA->date_of_birth?->format('Y-m-d') ?? 'Unknown', $personB->date_of_birth?->format('Y-m-d') ?? 'Unknown'],
            ['Address', $personA->address ?? 'Unknown', $personB->address ?? 'Unknown'],
            ['City/District', ($personA->city ?? '') . '/' . ($personA->district ?? ''), ($personB->city ?? '') . '/' . ($personB->district ?? '')],
        ]);

        $this->info("Relationship Type: " . ucfirst(str_replace('_', ' ', $relationship->relationship_type)));
        $this->info("Confidence Score: {$relationship->confidence_score}");
        $this->info("Discovery Method: " . ucfirst(str_replace('_', ' ', $relationship->discovery_method)));

        if ($relationship->metadata) {
            $this->info("Discovery Details: " . json_encode($relationship->metadata, JSON_PRETTY_PRINT));
        }

        return $this->choice(
            'What would you like to do with this relationship?',
            ['verify' => 'Verify (Correct)', 'reject' => 'Reject (Incorrect)', 'skip' => 'Skip for now', 'quit' => 'Quit verification'],
            'verify'
        );
    }

    private function reviewCrossOrgRelationship(CrossOrgRelationship $relationship): string
    {
        $this->newLine();
        $this->line('=== CROSS-ORG RELATIONSHIP REVIEW ===');

        $person = $relationship->person;
        $primaryOrg = $relationship->primaryAffiliation->organisation;
        $secondaryOrg = $relationship->secondaryAffiliation->organisation;

        $this->info("Person: {$person->given_name} {$person->family_name}");
        $this->newLine();

        $this->table(['Field', 'Primary Role', 'Secondary Role'], [
            ['Organization', $primaryOrg->legal_name, $secondaryOrg->legal_name],
            ['Category', $primaryOrg->category, $secondaryOrg->category],
            ['Role Type', $relationship->primaryAffiliation->role_type, $relationship->secondaryAffiliation->role_type],
            ['Start Date', $relationship->primaryAffiliation->start_date?->format('Y-m-d') ?? 'Unknown', $relationship->secondaryAffiliation->start_date?->format('Y-m-d') ?? 'Unknown'],
        ]);

        $this->info("Relationship Context: " . ($relationship->relationship_context ?? 'Auto-generated'));
        $this->info("Relationship Strength: " . ucfirst($relationship->relationship_strength));
        $this->info("Impact Score: {$relationship->impact_score}");
        $this->info("Discovery Method: " . ucfirst($relationship->discovery_method));

        if ($relationship->metadata) {
            $this->info("Discovery Details: " . json_encode($relationship->metadata, JSON_PRETTY_PRINT));
        }

        return $this->choice(
            'What would you like to do with this cross-org relationship?',
            ['verify' => 'Verify (Correct)', 'reject' => 'Reject (Incorrect)', 'skip' => 'Skip for now', 'quit' => 'Quit verification'],
            'verify'
        );
    }
}
