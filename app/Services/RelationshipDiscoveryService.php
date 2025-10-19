<?php

namespace App\Services;

use App\Models\Person;
use App\Models\PersonRelationship;
use App\Models\CrossOrgRelationship;
use App\Models\PersonAffiliation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RelationshipDiscoveryService
{
    private const MIN_CONFIDENCE_SCORE = 0.6;
    private const MAX_AGE_DIFFERENCE_PARENT_CHILD = 15; // Minimum age difference for parent-child
    private const SAME_ADDRESS_CONFIDENCE_BOOST = 0.3;
    private const SAME_CONTACT_CONFIDENCE_BOOST = 0.25;
    private const NAME_SIMILARITY_CONFIDENCE_BOOST = 0.2;

    /**
     * Run comprehensive relationship discovery
     */
    public function discoverAllRelationships(): array
    {
        $results = [
            'person_relationships' => 0,
            'cross_org_relationships' => 0,
            'errors' => []
        ];

        try {
            // Discover personal relationships
            $results['person_relationships'] = $this->discoverPersonalRelationships();

            // Discover cross-organizational relationships
            $results['cross_org_relationships'] = $this->discoverCrossOrgRelationships();

            Log::info('Relationship discovery completed', $results);

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error('Relationship discovery failed', ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Discover personal relationships (family, emergency contacts, etc.)
     */
    public function discoverPersonalRelationships(): int
    {
        $discovered = 0;

        // Address-based relationship discovery
        $discovered += $this->discoverByAddress();

        // Contact information matching
        $discovered += $this->discoverByContactInfo();

        // Name pattern analysis
        $discovered += $this->discoverByNamePatterns();

        // Temporal pattern analysis
        $discovered += $this->discoverByTemporalPatterns();

        return $discovered;
    }

    /**
     * Discover relationships based on shared addresses
     */
    private function discoverByAddress(): int
    {
        $discovered = 0;

        $addressGroups = Person::query()
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->where('status', 'active')
            ->groupBy('address', 'city', 'district')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('address', DB::raw('CONCAT(address, "|", COALESCE(city, ""), "|", COALESCE(district, ""))'))
            ->keys();

        foreach ($addressGroups as $addressKey) {
            [$address, $city, $district] = array_pad(explode('|', $addressKey), 3, '');

            $persons = Person::query()
                ->where('address', $address)
                ->where('city', $city ?: null)
                ->where('district', $district ?: null)
                ->where('status', 'active')
                ->get();

            $discovered += $this->analyzeAddressGroup($persons);
        }

        return $discovered;
    }

    /**
     * Analyze a group of people sharing the same address
     */
    private function analyzeAddressGroup(Collection $persons): int
    {
        $discovered = 0;

        foreach ($persons as $person1) {
            foreach ($persons as $person2) {
                if ($person1->id >= $person2->id) continue;

                // Check if relationship already exists
                if ($this->relationshipExists($person1->id, $person2->id)) {
                    continue;
                }

                $relationshipType = $this->determineRelationshipType($person1, $person2);
                $confidence = $this->calculateConfidence($person1, $person2, 'address_match');

                if ($confidence >= self::MIN_CONFIDENCE_SCORE && $relationshipType) {
                    PersonRelationship::createRelationship(
                        $person1->id,
                        $person2->id,
                        $relationshipType,
                        [
                            'confidence_score' => $confidence,
                            'discovery_method' => 'address_match',
                            'verification_status' => 'unverified',
                            'metadata' => [
                                'shared_address' => $person1->address,
                                'discovery_date' => now()->toDateString()
                            ]
                        ]
                    );
                    $discovered++;
                }
            }
        }

        return $discovered;
    }

    /**
     * Discover relationships by shared contact information
     */
    private function discoverByContactInfo(): int
    {
        $discovered = 0;

        // Shared phone numbers
        $phoneGroups = DB::table('phones')
            ->select('number')
            ->groupBy('number')
            ->havingRaw('COUNT(DISTINCT person_id) > 1')
            ->pluck('number');

        foreach ($phoneGroups as $phoneNumber) {
            $persons = Person::query()
                ->whereHas('phones', function ($query) use ($phoneNumber) {
                    $query->where('number', $phoneNumber);
                })
                ->where('status', 'active')
                ->get();

            $discovered += $this->analyzeContactGroup($persons, 'phone', $phoneNumber);
        }

        // Shared email addresses
        $emailGroups = DB::table('email_addresses')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(DISTINCT person_id) > 1')
            ->pluck('email');

        foreach ($emailGroups as $email) {
            $persons = Person::query()
                ->whereHas('emails', function ($query) use ($email) {
                    $query->where('email', $email);
                })
                ->where('status', 'active')
                ->get();

            $discovered += $this->analyzeContactGroup($persons, 'email', $email);
        }

        return $discovered;
    }

    /**
     * Analyze people sharing contact information
     */
    private function analyzeContactGroup(Collection $persons, string $contactType, string $contactValue): int
    {
        $discovered = 0;

        foreach ($persons as $person1) {
            foreach ($persons as $person2) {
                if ($person1->id >= $person2->id) continue;

                if ($this->relationshipExists($person1->id, $person2->id)) {
                    continue;
                }

                $relationshipType = $this->determineRelationshipType($person1, $person2);
                $confidence = $this->calculateConfidence($person1, $person2, 'contact_match');

                if ($confidence >= self::MIN_CONFIDENCE_SCORE && $relationshipType) {
                    PersonRelationship::createRelationship(
                        $person1->id,
                        $person2->id,
                        $relationshipType,
                        [
                            'confidence_score' => $confidence,
                            'discovery_method' => 'contact_match',
                            'verification_status' => 'unverified',
                            'metadata' => [
                                'shared_contact_type' => $contactType,
                                'shared_contact_value' => $contactValue,
                                'discovery_date' => now()->toDateString()
                            ]
                        ]
                    );
                    $discovered++;
                }
            }
        }

        return $discovered;
    }

    /**
     * Discover relationships by name patterns
     */
    private function discoverByNamePatterns(): int
    {
        $discovered = 0;

        // Group by family name
        $familyGroups = Person::query()
            ->where('status', 'active')
            ->groupBy('family_name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('family_name')
            ->filter();

        foreach ($familyGroups as $familyName) {
            $persons = Person::query()
                ->where('family_name', $familyName)
                ->where('status', 'active')
                ->orderBy('date_of_birth')
                ->get();

            $discovered += $this->analyzeFamilyNameGroup($persons);
        }

        return $discovered;
    }

    /**
     * Analyze people with the same family name
     */
    private function analyzeFamilyNameGroup(Collection $persons): int
    {
        $discovered = 0;

        foreach ($persons as $person1) {
            foreach ($persons as $person2) {
                if ($person1->id >= $person2->id) continue;

                if ($this->relationshipExists($person1->id, $person2->id)) {
                    continue;
                }

                $relationshipType = $this->determineRelationshipType($person1, $person2);
                $confidence = $this->calculateConfidence($person1, $person2, 'name_pattern');

                if ($confidence >= self::MIN_CONFIDENCE_SCORE && $relationshipType) {
                    PersonRelationship::createRelationship(
                        $person1->id,
                        $person2->id,
                        $relationshipType,
                        [
                            'confidence_score' => $confidence,
                            'discovery_method' => 'name_pattern',
                            'verification_status' => 'unverified',
                            'metadata' => [
                                'shared_family_name' => $person1->family_name,
                                'discovery_date' => now()->toDateString()
                            ]
                        ]
                    );
                    $discovered++;
                }
            }
        }

        return $discovered;
    }

    /**
     * Discover relationships by temporal patterns (enrollment dates, etc.)
     */
    private function discoverByTemporalPatterns(): int
    {
        $discovered = 0;

        // Find students enrolled around the same time with potential parent connections
        $studentParentPairs = DB::select("
            SELECT DISTINCT
                s.person_id as student_id,
                a.person_id as potential_parent_id
            FROM person_affiliations s
            JOIN person_affiliations a ON s.organisation_id = a.organisation_id
            JOIN persons sp ON s.person_id = sp.id
            JOIN persons ap ON a.person_id = ap.id
            WHERE s.role_type = 'STUDENT'
            AND a.role_type IN ('STAFF', 'ADMIN', 'PARENT')
            AND sp.family_name = ap.family_name
            AND ABS(DATEDIFF(s.start_date, a.start_date)) <= 90
            AND YEAR(CURDATE()) - YEAR(sp.date_of_birth) < 18
            AND YEAR(CURDATE()) - YEAR(ap.date_of_birth) >= 25
        ");

        foreach ($studentParentPairs as $pair) {
            if ($this->relationshipExists($pair->student_id, $pair->potential_parent_id)) {
                continue;
            }

            $student = Person::find($pair->student_id);
            $parent = Person::find($pair->potential_parent_id);

            if ($student && $parent) {
                $confidence = $this->calculateConfidence($student, $parent, 'temporal_pattern');

                if ($confidence >= self::MIN_CONFIDENCE_SCORE) {
                    PersonRelationship::createRelationship(
                        $student->id,
                        $parent->id,
                        'parent_child',
                        [
                            'confidence_score' => $confidence,
                            'discovery_method' => 'temporal_pattern',
                            'verification_status' => 'unverified',
                            'metadata' => [
                                'pattern_type' => 'student_parent_enrollment',
                                'discovery_date' => now()->toDateString()
                            ]
                        ]
                    );
                    $discovered++;
                }
            }
        }

        return $discovered;
    }

    /**
     * Discover cross-organizational relationships
     */
    public function discoverCrossOrgRelationships(): int
    {
        $discovered = 0;

        // Find people with multiple organizational affiliations
        $multiAffiliatedPersons = PersonAffiliation::query()
            ->select('person_id')
            ->groupBy('person_id')
            ->havingRaw('COUNT(DISTINCT organisation_id) > 1')
            ->pluck('person_id');

        foreach ($multiAffiliatedPersons as $personId) {
            $affiliations = PersonAffiliation::query()
                ->where('person_id', $personId)
                ->where('status', 'active')
                ->orderBy('start_date')
                ->get();

            $discovered += $this->analyzeCrossOrgConnections($personId, $affiliations);
        }

        return $discovered;
    }

    /**
     * Analyze cross-organizational connections for a person
     */
    private function analyzeCrossOrgConnections(int $personId, Collection $affiliations): int
    {
        $discovered = 0;

        foreach ($affiliations as $primary) {
            foreach ($affiliations as $secondary) {
                if ($primary->id >= $secondary->id) continue;

                // Check if cross-org relationship already exists
                if (CrossOrgRelationship::query()
                    ->where('person_id', $personId)
                    ->where('primary_affiliation_id', $primary->id)
                    ->where('secondary_affiliation_id', $secondary->id)
                    ->exists()) {
                    continue;
                }

                $strength = $this->calculateRelationshipStrength($primary, $secondary);
                $impactScore = $this->calculateCrossOrgImpactScore($primary, $secondary);

                CrossOrgRelationship::createCrossOrgRelationship(
                    $personId,
                    $primary->id,
                    $secondary->id,
                    [
                        'relationship_strength' => $strength,
                        'discovery_method' => 'automatic',
                        'impact_score' => $impactScore,
                        'metadata' => [
                            'time_gap_days' => $secondary->start_date ?
                                $secondary->start_date->diffInDays($primary->start_date) : 0,
                            'discovery_date' => now()->toDateString()
                        ]
                    ]
                );

                $discovered++;
            }
        }

        return $discovered;
    }

    /**
     * Determine relationship type between two persons
     */
    private function determineRelationshipType(Person $person1, Person $person2): ?string
    {
        $age1 = $person1->date_of_birth ? now()->diffInYears($person1->date_of_birth) : null;
        $age2 = $person2->date_of_birth ? now()->diffInYears($person2->date_of_birth) : null;

        // Parent-child relationship
        if ($age1 && $age2) {
            $ageDiff = abs($age1 - $age2);
            if ($ageDiff >= self::MAX_AGE_DIFFERENCE_PARENT_CHILD) {
                return 'parent_child';
            }

            // Sibling relationship (similar ages)
            if ($ageDiff <= 15 && $person1->family_name === $person2->family_name) {
                return 'sibling';
            }
        }

        // Spouse relationship (similar ages, different family names originally)
        if ($age1 && $age2 && abs($age1 - $age2) <= 10) {
            return 'spouse';
        }

        // Default to dependent relationship
        return 'dependent';
    }

    /**
     * Calculate confidence score for relationship
     */
    private function calculateConfidence(Person $person1, Person $person2, string $discoveryMethod): float
    {
        $confidence = 0.5; // Base confidence

        // Same address boost
        if ($person1->address === $person2->address && !empty($person1->address)) {
            $confidence += self::SAME_ADDRESS_CONFIDENCE_BOOST;
        }

        // Same city/district boost
        if ($person1->city === $person2->city && $person1->district === $person2->district) {
            $confidence += 0.1;
        }

        // Family name similarity boost
        if ($person1->family_name === $person2->family_name) {
            $confidence += self::NAME_SIMILARITY_CONFIDENCE_BOOST;
        }

        // Discovery method specific boosts
        switch ($discoveryMethod) {
            case 'address_match':
                $confidence += 0.2;
                break;
            case 'contact_match':
                $confidence += 0.25;
                break;
            case 'temporal_pattern':
                $confidence += 0.15;
                break;
        }

        return min(1.0, $confidence);
    }

    /**
     * Calculate relationship strength for cross-org connections
     */
    private function calculateRelationshipStrength(PersonAffiliation $primary, PersonAffiliation $secondary): string
    {
        $score = 0;

        // Role importance
        $importantRoles = ['ADMIN', 'MANAGER', 'DOCTOR', 'TEACHER'];
        if (in_array($primary->role_type, $importantRoles)) $score += 2;
        if (in_array($secondary->role_type, $importantRoles)) $score += 2;

        // Time overlap
        if ($primary->start_date && $secondary->start_date) {
            $daysDiff = abs($primary->start_date->diffInDays($secondary->start_date));
            if ($daysDiff <= 30) $score += 3;
            elseif ($daysDiff <= 90) $score += 2;
            elseif ($daysDiff <= 365) $score += 1;
        }

        // Organization type compatibility
        $primaryOrgType = $primary->organisation->category ?? '';
        $secondaryOrgType = $secondary->organisation->category ?? '';

        $compatibleTypes = [
            ['hospital', 'school'],
            ['school', 'sacco'],
            ['parish', 'sacco']
        ];

        foreach ($compatibleTypes as $combo) {
            if (($primaryOrgType === $combo[0] && $secondaryOrgType === $combo[1]) ||
                ($primaryOrgType === $combo[1] && $secondaryOrgType === $combo[0])) {
                $score += 2;
                break;
            }
        }

        if ($score >= 6) return 'strong';
        if ($score >= 3) return 'moderate';
        return 'weak';
    }

    /**
     * Calculate impact score for cross-org relationship
     */
    private function calculateCrossOrgImpactScore(PersonAffiliation $primary, PersonAffiliation $secondary): float
    {
        $score = 0.5; // Base score

        // High-impact role combinations (use indexed entries instead of using arrays as keys)
        $highImpactCombos = [
            [['STAFF', 'PATIENT'], 0.3],
            [['TEACHER', 'STUDENT'], 0.3],
            [['ADMIN', 'MEMBER'], 0.25],
            [['DOCTOR', 'PATIENT'], 0.35],
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

    /**
     * Check if relationship already exists between two persons
     */
    private function relationshipExists(int $personId1, int $personId2): bool
    {
        return PersonRelationship::query()
            ->where(function ($query) use ($personId1, $personId2) {
                $query->where('person_a_id', min($personId1, $personId2))
                      ->where('person_b_id', max($personId1, $personId2));
            })
            ->exists();
    }
}
