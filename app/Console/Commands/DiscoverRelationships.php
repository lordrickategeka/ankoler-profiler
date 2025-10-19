<?php

namespace App\Console\Commands;

use App\Services\RelationshipDiscoveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiscoverRelationships extends Command
{
    protected $signature = 'relationships:discover
                            {--type=all : Type of relationships to discover (all, personal, cross-org)}
                            {--limit=1000 : Maximum number of relationships to discover}
                            {--confidence=0.6 : Minimum confidence threshold}
                            {--dry-run : Run without saving results}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover relationships between persons and across organizations';

    private RelationshipDiscoveryService $discoveryService;

    public function __construct(RelationshipDiscoveryService $discoveryService)
    {
        parent::__construct();
        $this->discoveryService = $discoveryService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $isDryRun = $this->option('dry-run');

        $this->info('Starting relationship discovery...');
        $this->info("Type: {$type}");
        $this->info("Dry run: " . ($isDryRun ? 'Yes' : 'No'));

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }

        $startTime = microtime(true);

        try {
            DB::beginTransaction();

            $results = match($type) {
                'personal' => $this->discoverPersonalRelationships(),
                'cross-org' => $this->discoverCrossOrgRelationships(),
                'all' => $this->discoverAllRelationships(),
                default => throw new \InvalidArgumentException("Invalid type: {$type}")
            };

            if ($isDryRun) {
                DB::rollBack();
                $this->warn('Dry run completed - changes rolled back');
            } else {
                DB::commit();
                $this->info('Changes committed to database');
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->displayResults($results, $duration);

            return self::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error during discovery: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function discoverPersonalRelationships(): array
    {
        $this->info('Discovering personal relationships...');

        $bar = $this->output->createProgressBar(4);
        $bar->setFormat('verbose');

        $results = ['personal_relationships' => 0, 'methods' => []];

        // Address matching
        $bar->setMessage('Analyzing shared addresses...');
        $addressResults = $this->discoveryService->discoverByAddress();
        $results['methods']['address_match'] = $addressResults;
        $results['personal_relationships'] += $addressResults;
        $bar->advance();

        // Contact matching
        $bar->setMessage('Analyzing shared contacts...');
        $contactResults = $this->discoveryService->discoverByContactInfo();
        $results['methods']['contact_match'] = $contactResults;
        $results['personal_relationships'] += $contactResults;
        $bar->advance();

        // Name patterns
        $bar->setMessage('Analyzing name patterns...');
        $nameResults = $this->discoveryService->discoverByNamePatterns();
        $results['methods']['name_pattern'] = $nameResults;
        $results['personal_relationships'] += $nameResults;
        $bar->advance();

        // Temporal patterns
        $bar->setMessage('Analyzing temporal patterns...');
        $temporalResults = $this->discoveryService->discoverByTemporalPatterns();
        $results['methods']['temporal_pattern'] = $temporalResults;
        $results['personal_relationships'] += $temporalResults;
        $bar->advance();

        $bar->finish();
        $this->newLine();

        return $results;
    }

    private function discoverCrossOrgRelationships(): array
    {
        $this->info('Discovering cross-organizational relationships...');

        $results = [
            'cross_org_relationships' => $this->discoveryService->discoverCrossOrgRelationships()
        ];

        return $results;
    }

    private function discoverAllRelationships(): array
    {
        $personalResults = $this->discoverPersonalRelationships();
        $crossOrgResults = $this->discoverCrossOrgRelationships();

        return array_merge($personalResults, $crossOrgResults);
    }

    private function displayResults(array $results, float $duration): void
    {
        $this->newLine();
        $this->info('=== RELATIONSHIP DISCOVERY RESULTS ===');
        $this->info("Execution time: {$duration} seconds");
        $this->newLine();

        // Personal relationships
        if (isset($results['personal_relationships'])) {
            $this->info("Personal Relationships Discovered: {$results['personal_relationships']}");

            if (isset($results['methods'])) {
                $this->info('Breakdown by method:');
                foreach ($results['methods'] as $method => $count) {
                    $this->line("  - " . ucfirst(str_replace('_', ' ', $method)) . ": {$count}");
                }
            }
            $this->newLine();
        }

        // Cross-org relationships
        if (isset($results['cross_org_relationships'])) {
            $this->info("Cross-Org Relationships Discovered: {$results['cross_org_relationships']}");
            $this->newLine();
        }

        // Show some statistics
        $this->displayStatistics();

        // Show pending verifications
        $this->displayPendingVerifications();
    }

    private function displayStatistics(): void
    {
        $this->info('=== CURRENT STATISTICS ===');

        // Personal relationships stats
        $personalStats = DB::select("
            SELECT
                relationship_type,
                verification_status,
                COUNT(*) as count,
                AVG(confidence_score) as avg_confidence
            FROM person_relationships
            WHERE status = 'active'
            GROUP BY relationship_type, verification_status
            ORDER BY relationship_type, verification_status
        ");

        if ($personalStats) {
            $this->info('Personal Relationships:');
            $this->table(
                ['Type', 'Verification', 'Count', 'Avg Confidence'],
                array_map(function($stat) {
                    return [
                        ucfirst(str_replace('_', ' ', $stat->relationship_type)),
                        ucfirst($stat->verification_status),
                        $stat->count,
                        round($stat->avg_confidence, 2)
                    ];
                }, $personalStats)
            );
        }

        // Cross-org relationships stats
        $crossOrgStats = DB::select("
            SELECT
                relationship_strength,
                verified,
                COUNT(*) as count,
                AVG(impact_score) as avg_impact
            FROM cross_org_relationships
            WHERE status = 'active'
            GROUP BY relationship_strength, verified
            ORDER BY relationship_strength, verified
        ");

        if ($crossOrgStats) {
            $this->newLine();
            $this->info('Cross-Org Relationships:');
            $this->table(
                ['Strength', 'Verified', 'Count', 'Avg Impact'],
                array_map(function($stat) {
                    return [
                        ucfirst($stat->relationship_strength),
                        $stat->verified ? 'Yes' : 'No',
                        $stat->count,
                        round($stat->avg_impact, 2)
                    ];
                }, $crossOrgStats)
            );
        }
    }

    private function displayPendingVerifications(): void
    {
        $pendingPersonal = DB::table('person_relationships')
            ->where('verification_status', 'unverified')
            ->where('status', 'active')
            ->where('confidence_score', '>=', 0.8)
            ->count();

        $pendingCrossOrg = DB::table('cross_org_relationships')
            ->where('verified', false)
            ->where('status', 'active')
            ->where('impact_score', '>=', 0.7)
            ->count();

        if ($pendingPersonal > 0 || $pendingCrossOrg > 0) {
            $this->newLine();
            $this->info('=== PENDING VERIFICATIONS ===');
            $this->info("High-confidence personal relationships: {$pendingPersonal}");
            $this->info("High-impact cross-org relationships: {$pendingCrossOrg}");
            $this->newLine();
            $this->comment('Run "php artisan relationships:verify" to review pending verifications');
        }
    }
}
