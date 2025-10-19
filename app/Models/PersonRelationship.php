<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PersonRelationship extends Model
{
    use HasFactory;

    protected $fillable = [
        'relationship_id',
        'person_a_id',
        'person_b_id',
        'relationship_type',
        'direction',
        'is_primary',
        'confidence_score',
        'discovery_method',
        'start_date',
        'end_date',
        'status',
        'verification_status',
        'verified_at',
        'verified_by',
        'notes',
        'metadata',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'notes' => 'array',
        'metadata' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'verified_at' => 'datetime',
        'is_primary' => 'boolean',
        'confidence_score' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->relationship_id)) {
                $model->relationship_id = 'REL-' . strtoupper(Str::random(10));
            }
        });
    }

    // Relationships
    public function personA(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_a_id');
    }

    public function personB(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_b_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeHighConfidence(Builder $query, float $threshold = 0.8): Builder
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    public function scopeForPerson(Builder $query, int $personId): Builder
    {
        return $query->where(function ($q) use ($personId) {
            $q->where('person_a_id', $personId)
              ->orWhere('person_b_id', $personId);
        });
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('relationship_type', $type);
    }

    // Helper methods
    public function getOtherPerson(int $personId): ?Person
    {
        if ($this->person_a_id === $personId) {
            return $this->personB;
        } elseif ($this->person_b_id === $personId) {
            return $this->personA;
        }

        return null;
    }

    public function getRelationshipDirection(int $personId): ?string
    {
        if ($this->direction === 'bidirectional') {
            return 'bidirectional';
        }

        if ($this->person_a_id === $personId && $this->direction === 'a_to_b') {
            return 'outgoing';
        }

        if ($this->person_b_id === $personId && $this->direction === 'a_to_b') {
            return 'incoming';
        }

        if ($this->person_a_id === $personId && $this->direction === 'b_to_a') {
            return 'incoming';
        }

        if ($this->person_b_id === $personId && $this->direction === 'b_to_a') {
            return 'outgoing';
        }

        return null;
    }

    public function markAsVerified(int $userId): bool
    {
        return $this->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $userId,
            'confidence_score' => 1.00
        ]);
    }

    public function markAsRejected(int $userId): bool
    {
        return $this->update([
            'verification_status' => 'rejected',
            'verified_at' => now(),
            'verified_by' => $userId,
            'status' => 'inactive'
        ]);
    }

    // Static helper methods
    public static function getRelationshipTypes(): array
    {
        return [
            'parent_child' => 'Parent-Child',
            'spouse' => 'Spouse',
            'sibling' => 'Sibling',
            'guardian_ward' => 'Guardian-Ward',
            'emergency_contact' => 'Emergency Contact',
            'next_of_kin' => 'Next of Kin',
            'dependent' => 'Dependent',
            'colleague' => 'Colleague',
            'business_partner' => 'Business Partner'
        ];
    }

    public static function getDiscoveryMethods(): array
    {
        return [
            'manual' => 'Manual Entry',
            'address_match' => 'Address Matching',
            'contact_match' => 'Contact Information Matching',
            'name_pattern' => 'Name Pattern Analysis',
            'temporal_pattern' => 'Temporal Pattern Analysis',
            'user_import' => 'User Import'
        ];
    }

    public static function createRelationship(
        int $personAId,
        int $personBId,
        string $relationshipType,
        array $options = []
    ): self {
        // Ensure consistent ordering (smaller ID first)
        if ($personAId > $personBId) {
            [$personAId, $personBId] = [$personBId, $personAId];
        }

        return self::create(array_merge([
            'person_a_id' => $personAId,
            'person_b_id' => $personBId,
            'relationship_type' => $relationshipType,
            'confidence_score' => 0.75,
            'verification_status' => 'unverified',
            'status' => 'active'
        ], $options));
    }

    // Query helpers for finding potential relationships
    public static function findPotentialByAddress(int $personId): Builder
    {
        return static::query()
            ->join('persons as p1', 'person_relationships.person_a_id', '=', 'p1.id')
            ->join('persons as p2', 'person_relationships.person_b_id', '=', 'p2.id')
            ->where(function ($query) use ($personId) {
                $query->where('person_a_id', $personId)
                      ->orWhere('person_b_id', $personId);
            })
            ->where('discovery_method', 'address_match')
            ->where('verification_status', 'unverified');
    }

    public static function findFamilyNetwork(int $personId, int $depth = 2): array
    {
        $visited = [];
        $network = [];

        return self::buildFamilyNetwork($personId, $depth, $visited, $network);
    }

    private static function buildFamilyNetwork(int $personId, int $depth, array &$visited, array &$network): array
    {
        if ($depth <= 0 || in_array($personId, $visited)) {
            return $network;
        }

        $visited[] = $personId;

        $relationships = self::forPerson($personId)
            ->active()
            ->whereIn('relationship_type', ['parent_child', 'spouse', 'sibling', 'guardian_ward'])
            ->with(['personA', 'personB'])
            ->get();

        foreach ($relationships as $relationship) {
            $otherPerson = $relationship->getOtherPerson($personId);
            if ($otherPerson && !in_array($otherPerson->id, $visited)) {
                $network[] = [
                    'person' => $otherPerson,
                    'relationship' => $relationship,
                    'distance' => 3 - $depth
                ];

                // Recursively find connections
                self::buildFamilyNetwork($otherPerson->id, $depth - 1, $visited, $network);
            }
        }

        return $network;
    }
}
