<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\PersonRelationship;
use App\Models\CrossOrgRelationship;
use App\Models\PersonAffiliation;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RelationshipSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting relationship seeding...');

        // Seed personal relationships
        $this->seedPersonalRelationships();

        // Seed cross-organizational relationships
        $this->seedCrossOrgRelationships();

        $this->command->info('Relationship seeding completed!');
    }

    private function seedPersonalRelationships(): void
    {
        $this->command->info('Seeding personal relationships...');

        // Get existing persons for creating relationships
        $persons = Person::where('status', 'active')->get();

        if ($persons->count() < 10) {
            $this->command->warn('Not enough persons in database. Creating some first...');
            $this->createSamplePersons();
            $persons = Person::where('status', 'active')->get();
        }

        $relationshipCount = 0;

        // Create family clusters
        $familyClusters = $this->createFamilyClusters($persons->take(60));
        $relationshipCount += $familyClusters;

        // Create address-based relationships
        $addressRelationships = $this->createAddressBasedRelationships($persons);
        $relationshipCount += $addressRelationships;

        // Create contact-based relationships
        $contactRelationships = $this->createContactBasedRelationships($persons);
        $relationshipCount += $contactRelationships;

        // Create some manual/verified relationships
        $manualRelationships = $this->createManualRelationships($persons->take(20));
        $relationshipCount += $manualRelationships;

        $this->command->info("Created {$relationshipCount} personal relationships");
    }

    private function createFamilyClusters(mixed $persons): int
    {
        $created = 0;
        $familyNames = ['Mugisha', 'Byarugaba', 'Katende', 'Nakato', 'Wasswa', 'Babirye', 'Tumusiime', 'Asiimwe'];

        foreach ($familyNames as $familyName) {
            // Get persons with this family name
            $familyMembers = $persons->where('family_name', $familyName);

            if ($familyMembers->count() >= 2) {
                // Create parent-child relationships
                $adults = $familyMembers->filter(function($person) {
                    return $person->date_of_birth && now()->diffInYears($person->date_of_birth) >= 25;
                });

                $children = $familyMembers->filter(function($person) {
                    return $person->date_of_birth && now()->diffInYears($person->date_of_birth) < 18;
                });

                foreach ($adults->take(2) as $adult) {
                    foreach ($children->take(3) as $child) {
                        if (!$this->relationshipExists($adult->id, $child->id)) {
                            PersonRelationship::createRelationship(
                                $adult->id,
                                $child->id,
                                'parent_child',
                                [
                                    'confidence_score' => rand(85, 95) / 100,
                                    'discovery_method' => 'name_pattern',
                                    'verification_status' => rand(0, 1) ? 'verified' : 'unverified',
                                    'verified_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                                    'verified_by' => rand(0, 1) ? 1 : null,
                                    'metadata' => [
                                        'shared_family_name' => $familyName,
                                        'age_difference' => abs(now()->diffInYears($adult->date_of_birth) - now()->diffInYears($child->date_of_birth))
                                    ]
                                ]
                            );
                            $created++;
                        }
                    }
                }

                // Create sibling relationships among children
                $childrenArray = $children->values()->toArray(); // Reindex array
                if (count($childrenArray) < 2) {
                    // Not enough children to create sibling relationships
                    continue;
                }
                for ($i = 0; $i < count($childrenArray) - 1; $i++) {
                    for ($j = $i + 1; $j < count($childrenArray); $j++) {
                        if (!$this->relationshipExists($childrenArray[$i]['id'], $childrenArray[$j]['id'])) {
                            PersonRelationship::createRelationship(
                                $childrenArray[$i]['id'],
                                $childrenArray[$j]['id'],
                                'sibling',
                                [
                                    'confidence_score' => rand(80, 95) / 100,
                                    'discovery_method' => 'name_pattern',
                                    'verification_status' => rand(0, 1) ? 'verified' : 'unverified',
                                    'metadata' => [
                                        'shared_family_name' => $familyName,
                                        'discovery_date' => now()->subDays(rand(1, 60))->toDateString()
                                    ]
                                ]
                            );
                            $created++;
                        }
                    }
                }

                // Create spouse relationships among adults
                $adultsArray = $adults->values()->toArray(); // Reindex array
                if (count($adultsArray) >= 2) {
                    PersonRelationship::createRelationship(
                        $adultsArray[0]['id'],
                        $adultsArray[1]['id'],
                        'spouse',
                        [
                            'confidence_score' => rand(90, 100) / 100,
                            'discovery_method' => 'address_match',
                            'verification_status' => 'verified',
                            'verified_at' => now()->subDays(rand(1, 10)),
                            'verified_by' => 1,
                            'metadata' => [
                                'shared_address' => $adults->first()->address,
                                'relationship_duration_years' => rand(5, 25)
                            ]
                        ]
                    );
                    $created++;
                }
            }
        }

        return $created;
    }

    private function createAddressBasedRelationships($persons): int
    {
        $created = 0;

        // Group persons by address
        $addressGroups = $persons->groupBy(function($person) {
            return $person->address . '|' . $person->city . '|' . $person->district;
        });

        foreach ($addressGroups as $addressKey => $group) {
            if ($group->count() > 1) {
                $groupArray = $group->toArray();

                for ($i = 0; $i < count($groupArray) - 1; $i++) {
                    for ($j = $i + 1; $j < count($groupArray); $j++) {
                        $person1 = $groupArray[$i];
                        $person2 = $groupArray[$j];

                        if (!$this->relationshipExists($person1['id'], $person2['id'])) {
                            // Determine relationship type based on ages and names
                            $relationshipType = $this->guessRelationshipType($person1, $person2);

                            if ($relationshipType) {
                                PersonRelationship::createRelationship(
                                    $person1['id'],
                                    $person2['id'],
                                    $relationshipType,
                                    [
                                        'confidence_score' => rand(70, 90) / 100,
                                        'discovery_method' => 'address_match',
                                        'verification_status' => 'unverified',
                                        'metadata' => [
                                            'shared_address' => $person1['address'],
                                            'discovery_date' => now()->subDays(rand(1, 30))->toDateString()
                                        ]
                                    ]
                                );
                                $created++;
                            }
                        }
                    }
                }
            }
        }

        return $created;
    }

    private function createContactBasedRelationships($persons): int
    {
        $created = 0;

        // Simulate some persons sharing contact information
        $sharedContacts = [
            [
                'persons' => $persons->random(3)->pluck('id')->toArray(),
                'contact_type' => 'phone',
                'contact_value' => '+256701234567'
            ],
            [
                'persons' => $persons->random(2)->pluck('id')->toArray(),
                'contact_type' => 'email',
                'contact_value' => 'family@example.com'
            ],
            [
                'persons' => $persons->random(4)->pluck('id')->toArray(),
                'contact_type' => 'phone',
                'contact_value' => '+256702345678'
            ]
        ];

        foreach ($sharedContacts as $contactGroup) {
            $personIds = $contactGroup['persons'];

            for ($i = 0; $i < count($personIds) - 1; $i++) {
                for ($j = $i + 1; $j < count($personIds); $j++) {
                    if (!$this->relationshipExists($personIds[$i], $personIds[$j])) {
                        PersonRelationship::createRelationship(
                            $personIds[$i],
                            $personIds[$j],
                            'emergency_contact',
                            [
                                'confidence_score' => rand(60, 85) / 100,
                                'discovery_method' => 'contact_match',
                                'verification_status' => 'unverified',
                                'metadata' => [
                                    'shared_contact_type' => $contactGroup['contact_type'],
                                    'shared_contact_value' => $contactGroup['contact_value'],
                                    'discovery_date' => now()->subDays(rand(1, 45))->toDateString()
                                ]
                            ]
                        );
                        $created++;
                    }
                }
            }
        }

        return $created;
    }

    private function createManualRelationships($persons): int
    {
        $created = 0;

        // Create some high-confidence manual relationships
        for ($i = 0; $i < 10; $i++) {
            $person1 = $persons->random();
            $person2 = $persons->where('id', '!=', $person1->id)->random();

            if (!$this->relationshipExists($person1->id, $person2->id)) {
                $relationshipTypes = ['colleague', 'business_partner', 'next_of_kin'];

                PersonRelationship::createRelationship(
                    $person1->id,
                    $person2->id,
                    $relationshipTypes[array_rand($relationshipTypes)],
                    [
                        'confidence_score' => 1.0,
                        'discovery_method' => 'manual',
                        'verification_status' => 'verified',
                        'verified_at' => now()->subDays(rand(1, 5)),
                        'verified_by' => 1,
                        'created_by' => 1,
                        'metadata' => [
                            'manual_entry' => true,
                            'entry_reason' => 'Diocese admin verification'
                        ]
                    ]
                );
                $created++;
            }
        }

        return $created;
    }

    private function seedCrossOrgRelationships(): void
    {
        $this->command->info('Seeding cross-organizational relationships...');

        // Get persons with multiple affiliations
        $multiAffiliatedPersons = Person::whereHas('affiliations', function($query) {
            $query->where('status', 'active');
        }, '>=', 2)->with('affiliations.Organization')->get();

        $created = 0;

        foreach ($multiAffiliatedPersons as $person) {
            $affiliations = $person->affiliations->where('status', 'active');

            if ($affiliations->count() >= 2) {
                $affiliationModels = $affiliations->values();

                for ($i = 0; $i < $affiliationModels->count() - 1; $i++) {
                    for ($j = $i + 1; $j < $affiliationModels->count(); $j++) {
                        $primary = $affiliationModels->get($i);
                        $secondary = $affiliationModels->get($j);

                        // Use updateOrCreate to avoid duplicate unique constraint errors
                        $strength = $this->calculateRelationshipStrength($primary, $secondary);
                        $impactScore = $this->calculateImpactScore($primary, $secondary);

                        CrossOrgRelationship::updateOrCreate(
                            [
                                'person_id' => $person->id,
                                'primary_affiliation_id' => $primary->id,
                                'secondary_affiliation_id' => $secondary->id,
                            ],
                            [
                                'relationship_strength' => $strength,
                                'discovery_method' => 'automatic',
                                'impact_score' => $impactScore,
                                'verified' => rand(0, 3) == 0, // 25% chance of being verified
                                'verified_at' => rand(0, 3) == 0 ? now()->subDays(rand(1, 20)) : null,
                                'verified_by' => rand(0, 3) == 0 ? 1 : null,
                                'metadata' => [
                                    'auto_discovery' => true,
                                    'time_gap_days' => $this->calculateTimeGap($primary, $secondary),
                                    'discovery_date' => now()->subDays(rand(1, 90))->toDateString()
                                ],
                                'status' => 'active',
                            ]
                        );
                        $created++;
                    }
                }
            }
        }

        // Create some specific high-impact scenarios
        $created += $this->createSpecificScenarios();

        $this->command->info("Created {$created} cross-organizational relationships");
    }

    private function createSpecificScenarios(): int
    {
        $created = 0;

        // Scenario 1: Doctor who is also a parent at school
        $this->createScenario('DOCTOR', 'hospital', 'PARENT', 'school', $created);

        // Scenario 2: Teacher who is also a SACCO member
        $this->createScenario('TEACHER', 'school', 'MEMBER', 'sacco', $created);

        // Scenario 3: Hospital staff who is also a parish member
        $this->createScenario('STAFF', 'hospital', 'MEMBER', 'parish', $created);

        // Scenario 4: SACCO staff who is also a student
        $this->createScenario('STAFF', 'sacco', 'STUDENT', 'school', $created);

        return $created;
    }

    private function createScenario(string $role1, string $orgType1, string $role2, string $orgType2, int &$created): void
    {
        // Find organizations of the specified types
        $org1 = Organization::where('category', $orgType1)->where('is_active', 1)->first();
        $org2 = Organization::where('category', $orgType2)->where('is_active', 1)->first();

        if (!$org1 || !$org2) return;

        // Get a person with role1 at org1
        $affiliation1 = PersonAffiliation::where('organization_id', $org1->id)
            ->where('role_type', $role1)
            ->where('status', 'active')
            ->first();

        if (!$affiliation1) return;

        // Create a second affiliation for the same person at org2
        $existingAffiliation2 = PersonAffiliation::where('person_id', $affiliation1->person_id)
            ->where('organization_id', $org2->id)
            ->where('role_type', $role2)
            ->first();

        if (!$existingAffiliation2) {
            $affiliation2 = PersonAffiliation::create([
                'affiliation_id' => 'AFF-' . str_pad(PersonAffiliation::max('id') + 1, 6, '0', STR_PAD_LEFT),
                'person_id' => $affiliation1->person_id,
                'organization_id' => $org2->id,
                'role_type' => $role2,
                'start_date' => now()->subMonths(rand(1, 24)),
                'status' => 'active',
                'created_by' => 1
            ]);
        } else {
            $affiliation2 = $existingAffiliation2;
        }

        // Use updateOrCreate to avoid duplicate unique constraint errors
        CrossOrgRelationship::updateOrCreate(
            [
                'person_id' => $affiliation1->person_id,
                'primary_affiliation_id' => $affiliation1->id,
                'secondary_affiliation_id' => $affiliation2->id,
            ],
            [
                'relationship_strength' => 'strong',
                'discovery_method' => 'automatic',
                'impact_score' => rand(75, 95) / 100,
                'verified' => rand(0, 1),
                'metadata' => [
                    'scenario_type' => "{$role1}_at_{$orgType1}_{$role2}_at_{$orgType2}",
                    'high_impact_scenario' => true
                ],
                'status' => 'active',
            ]
        );

        $created++;
    }

    private function createSamplePersons(): void
    {
        $this->command->info('Creating sample persons for relationships...');

        $samplePersons = [
            [
                'given_name' => 'Robert',
                'family_name' => 'Mugisha',
                'date_of_birth' => '1975-05-15',
                'gender' => 'male',
                'address' => '123 Kampala Road',
                'city' => 'Kampala',
                'district' => 'Central'
            ],
            [
                'given_name' => 'Mary',
                'family_name' => 'Mugisha',
                'date_of_birth' => '1978-08-22',
                'gender' => 'female',
                'address' => '123 Kampala Road',
                'city' => 'Kampala',
                'district' => 'Central'
            ],
            [
                'given_name' => 'Sarah',
                'family_name' => 'Mugisha',
                'date_of_birth' => '2010-03-10',
                'gender' => 'female',
                'address' => '123 Kampala Road',
                'city' => 'Kampala',
                'district' => 'Central'
            ],
            [
                'given_name' => 'David',
                'family_name' => 'Mugisha',
                'date_of_birth' => '2012-11-05',
                'gender' => 'male',
                'address' => '123 Kampala Road',
                'city' => 'Kampala',
                'district' => 'Central'
            ]
        ];

        foreach ($samplePersons as $personData) {
            Person::create(array_merge($personData, [
                'global_identifier' => Str::uuid(),
                'status' => 'active',
                'country' => 'Uganda',
                'created_by' => 1
            ]));
        }
    }

    private function relationshipExists(int $personId1, int $personId2): bool
    {
        return PersonRelationship::where(function ($query) use ($personId1, $personId2) {
            $query->where('person_a_id', min($personId1, $personId2))
                  ->where('person_b_id', max($personId1, $personId2));
        })->exists();
    }

    private function guessRelationshipType(array $person1, array $person2): ?string
    {
        $age1 = $person1['date_of_birth'] ? now()->diffInYears($person1['date_of_birth']) : null;
        $age2 = $person2['date_of_birth'] ? now()->diffInYears($person2['date_of_birth']) : null;

        if ($age1 && $age2) {
            $ageDiff = abs($age1 - $age2);

            // Parent-child relationship
            if ($ageDiff >= 15 && $person1['family_name'] === $person2['family_name']) {
                return 'parent_child';
            }

            // Sibling relationship
            if ($ageDiff <= 15 && $person1['family_name'] === $person2['family_name']) {
                return 'sibling';
            }

            // Spouse relationship
            if ($ageDiff <= 10 && $person1['family_name'] === $person2['family_name']) {
                return 'spouse';
            }
        }

        // Default to dependent for same address
        return 'dependent';
    }

    private function calculateRelationshipStrength(PersonAffiliation $primary, PersonAffiliation $secondary): string
    {
        $score = 0;

        // Role importance
        $importantRoles = ['ADMIN', 'MANAGER', 'DOCTOR', 'TEACHER'];
        if (in_array($primary->role_type, $importantRoles)) $score += 2;
        if (in_array($secondary->role_type, $importantRoles)) $score += 2;

        // Time proximity
        if (!empty($primary->start_date) && !empty($secondary->start_date)) {
            $daysDiff = abs(strtotime($primary->start_date) - strtotime($secondary->start_date)) / (60 * 60 * 24);
            if ($daysDiff <= 30) $score += 3;
            elseif ($daysDiff <= 90) $score += 2;
            elseif ($daysDiff <= 365) $score += 1;
        }

        // Random factor
        $score += rand(0, 2);

        if ($score >= 6) return 'strong';
        if ($score >= 3) return 'moderate';
        return 'weak';
    }

    private function calculateImpactScore(PersonAffiliation $primary, PersonAffiliation $secondary): float
    {
        $score = 0.5;

        // High-impact combinations
        $highImpactCombos = [
            [['STAFF', 'PATIENT'], 0.3],
            [['TEACHER', 'STUDENT'], 0.3],
            [['ADMIN', 'MEMBER'], 0.25],
            [['DOCTOR', 'PATIENT'], 0.35]
        ];

        foreach ($highImpactCombos as $entry) {
            $combo = $entry[0];
            $boost = $entry[1];

            if (($primary->role_type === $combo[0] && $secondary->role_type === $combo[1]) ||
                ($primary->role_type === $combo[1] && $secondary->role_type === $combo[0])) {
                $score += $boost;
                break;
            }
        }

        return min(1.0, $score);
    }


    private function calculateTimeGap(PersonAffiliation $primary, PersonAffiliation $secondary): int
    {
        if (empty($primary->start_date) || empty($secondary->start_date)) {
            return 0;
        }
        return abs(strtotime($primary->start_date) - strtotime($secondary->start_date)) / (60 * 60 * 24);
    }
}
