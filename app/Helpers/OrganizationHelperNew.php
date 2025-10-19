<?php

namespace App\Helpers;

use App\Models\Organisation;
use Illuminate\Support\Facades\Auth;

class OrganizationHelperNew
{
    /**
     * Get the current organization for the authenticated user
     */
    public static function getCurrentOrganization(): ?Organisation
    {
        // First try to get from session
        $organisationId = session('current_organisation_id');

        if (!$organisationId && Auth::check()) {
            // Fall back to user's default organization
            $organisationId = Auth::user()->organisation_id;
        }

        if ($organisationId) {
            return Organisation::find($organisationId);
        }

        return null;
    }

    /**
     * Get current organisation ID from session or user
     */
    public static function getCurrentOrganizationId(): ?int
    {
        return session('current_organisation_id', Auth::user()->organisation_id ?? null);
    }

    /**
     * Get current organisation name
     */
    public static function getCurrentOrganizationName(): string
    {
        $org = self::getCurrentOrganization();
        return $org ? $org->name : 'No organisation';
    }

    /**
     * Set the current organization in session
     */
    public static function setCurrentOrganization(int $organisationId): void
    {
        $organisation = Organisation::find($organisationId);

        if ($organisation && self::canAccessOrganization($organisationId)) {
            session([
                'current_organisation_id' => $organisationId,
                'current_organisation_name' => $organisation->name
            ]);
        }
    }

    /**
     * Check if current user can access an organisation
     */
    public static function canAccessOrganization(int $organisationId): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Check if it's the user's default organization
        if ($user->organisation_id === $organisationId) {
            return true;
        }

        // Check if user has access through affiliations or other relationships
        // For now, just return true if user exists - you can expand this logic
        return true;
    }

    /**
     * Get all organisations current user can access
     */
    public static function getUserAccessibleOrganizations()
    {
        if (!Auth::check()) {
            return collect();
        }

        $user = Auth::user();

        // Get user's primary organization
        $organizations = collect();

        if ($user->organisation_id) {
            $primaryOrg = Organisation::find($user->organisation_id);
            if ($primaryOrg) {
                $organizations->push($primaryOrg);
            }
        }

        // Add any additional organizations the user has access to
        // This depends on your business logic and relationships

        return $organizations;
    }

    /**
     * Switch to a different organization
     */
    public static function switchOrganization(int $organisationId): bool
    {
        if (self::canAccessOrganization($organisationId)) {
            self::setCurrentOrganization($organisationId);
            return true;
        }

        return false;
    }

    /**
     * Clear current organization from session
     */
    public static function clearCurrentOrganization(): void
    {
        session()->forget(['current_organisation_id', 'current_organisation_name']);
    }
}
