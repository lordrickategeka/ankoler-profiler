<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'organization_id',
        'subject',
        'content',
        'category',
        'supported_channels',
        'variables',
        'is_active',
        'is_shared',
        'usage_count',
        'last_used_at'
    ];

    protected $casts = [
        'supported_channels' => 'array',
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
        'last_used_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function Organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function render(Person $person, array $extraVariables = []): array
    {
        $variables = array_merge(
            $this->getPersonVariables($person),
            $extraVariables
        );

        $content = $this->content;
        $subject = $this->subject;

        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $content = str_replace($placeholder, $value, $content);
            if ($subject) {
                $subject = str_replace($placeholder, $value, $subject);
            }
        }

        return [
            'subject' => $subject,
            'content' => $content,
            'variables_used' => array_keys($variables)
        ];
    }

    private function getPersonVariables(Person $person): array
    {
        $variables = [
            'given_name' => $person->given_name,
            'family_name' => $person->family_name,
            'full_name' => trim($person->given_name . ' ' . $person->family_name),
            'person_id' => $person->person_id,
            'gender' => $person->gender,
        ];

        $affiliation = $person->affiliations->first();
        if ($affiliation) {
            $variables['role_title'] = $affiliation->role_title;
            $variables['Organization_name'] = $affiliation->Organization->legal_name ?? '';
        }

        $primaryPhone = $person->primaryPhone();
        if ($primaryPhone) {
            $variables['phone_number'] = $primaryPhone->number;
        }

        $primaryEmail = $person->primaryEmail();
        if ($primaryEmail) {
            $variables['email_address'] = $primaryEmail->email;
        }

        return $variables;
    }

    public function supportsChannel(string $channel): bool
    {
        return in_array($channel, $this->supported_channels);
    }

    public function markAsUsed(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAccessibleBy($query, $userId, $OrganizationId)
    {
        return $query->where(function ($q) use ($userId, $OrganizationId) {
            $q->where('user_id', $userId)
              ->orWhere(function ($q2) use ($OrganizationId) {
                  $q2->where('organization_id', $OrganizationId)
                     ->where('is_shared', true);
              });
        });
    }
}
