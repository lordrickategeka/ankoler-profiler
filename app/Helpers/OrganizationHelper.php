<?php
use Illuminate\Support\Facades\Auth;
// app/Helpers/OrganizationHelper.php

use App\Models\Organization;

if (!function_exists('current_organization_id')) {
    /**
     * Get current Organization ID from session
     */
    function current_organization_id()
    {
        $user = Auth::user();
        if ($user && property_exists($user, 'organization_id')) {
            return session('current_organization_id', $user->organization_id);
        }
        return session('current_organization_id');
    }
}

if (!function_exists('current_Organization')) {
    /**
     * Get current Organization model
     */

    function current_Organization()
    {
        $id = current_organization_id();
        return $id ? Organization::find($id) : null;
    }
}

if (!function_exists('current_Organization_name')) {
    /**
     * Get current Organization name
     */
    function current_Organization_name()
    {
        return session('current_Organization_name', 'No Organization');
    }
}

if (!function_exists('can_access_Organization')) {
    /**
     * Check if current user can access an Organization
     */
    function can_access_Organization($OrganizationId)
    {
        $user = Auth::user();
        return $user && method_exists($user, 'canAccessOrganization') && $user->canAccessOrganization($OrganizationId);
    }
}

if (!function_exists('user_accessible_Organizations')) {
    // Get all Organizations current user can access
    function user_accessible_Organizations()
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'accessibleOrganizations')) {
            return $user->accessibleOrganizations();
        }
        return collect();
    }
}

// American spelling variants for compatibility
// (Removed duplicate function definitions)

if (!function_exists('user_current_organization')) {
    function user_current_organization()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return null;
        }

        // For Super Admin, we need a different approach since they manage multiple organizations
        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            // 1. First check session (temporary working organization)
            $sessionOrgId = session('current_organization_id');
            if ($sessionOrgId && \App\Models\Organization::where('id', $sessionOrgId)->exists()) {
                return \App\Models\Organization::find($sessionOrgId);
            }

            // 2. Check if Super Admin has any person affiliations (they might also be staff somewhere)
            if (isset($user->person_id) && $user->person_id) {
                $affiliation = \App\Models\PersonAffiliation::where('person_id', $user->person_id)
                    ->where('status', 'active')
                    ->with('Organization')
                    ->orderBy('start_date', 'desc')
                    ->first();

                if ($affiliation && $affiliation->Organization) {
                    // Store this as session for consistency
                    session(['current_organization_id' => $affiliation->Organization->id]);
                    return $affiliation->Organization;
                }
            }

            // 3. Fallback to first active organization
            $firstOrg = \App\Models\Organization::where('is_active', true)->first() ?? \App\Models\Organization::first();
            if ($firstOrg) {
                session(['current_organization_id' => $firstOrg->id]);
                return $firstOrg;
            }

            return null;
        }

        // For regular users - use database affiliations first, session as fallback
        // 1. Use persons table: find person by user_id and return their organization_id
        $person = \App\Models\Person::where('user_id', $user->id)->first();
        if ($person && $person->organization_id) {
            $org = \App\Models\Organization::find($person->organization_id);
            if ($org) {
                return $org;
            }
        }

        // 2. Try session organization (if user switched context)
        $sessionOrgId = session('current_organization_id');
        if ($sessionOrgId) {
            $org = \App\Models\Organization::find($sessionOrgId);
            if ($org) {
                return $org;
            }
        }

        // 3. Try direct organization relationship (if exists)
        if (isset($user->organization_id) && $user->organization_id) {
            $org = \App\Models\Organization::find($user->organization_id);
            if ($org) {
                return $org;
            }
        }

        return null;
    }
}

if (!function_exists('set_user_current_organization')) {
    function set_user_current_organization($organizationId)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return false;
        }

        // Validate that the organization exists
        $organization = \App\Models\Organization::find($organizationId);
        if (!$organization) {
            return false;
        }

        // For Super Admin, just set the session (they can work with any organization)
        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            session(['current_organization_id' => $organizationId]);
            session(['current_organization_name' => $organization->display_name ?? $organization->legal_name]);
            return true;
        }

        // For regular users, verify they have access to this organization through affiliations
        if (isset($user->person_id) && $user->person_id) {
            $hasAccess = \App\Models\PersonAffiliation::where('person_id', $user->person_id)
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->exists();

            if ($hasAccess) {
                session(['current_organization_id' => $organizationId]);
                session(['current_organization_name' => $organization->display_name ?? $organization->legal_name]);
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('get_user_accessible_organizations')) {
    function get_user_accessible_organizations()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return collect();
        }

        // Super Admin can access all organizations
        if (method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
            return \App\Models\Organization::where('is_active', true)
                ->orderBy('display_name')
                ->get();
        }

        // For regular users, get organizations through person affiliations
        if (isset($user->person_id) && $user->person_id) {
            return \App\Models\Organization::whereHas('affiliations', function($query) use ($user) {
                $query->where('person_id', $user->person_id)
                      ->where('status', 'active');
            })
            ->where('is_active', true)
            ->orderBy('display_name')
            ->get();
        }

        // Fallback: if user has direct organization relationship
        if (isset($user->organization_id) && $user->organization_id) {
            $org = \App\Models\Organization::find($user->organization_id);
            return $org ? collect([$org]) : collect();
        }

        // No organizations accessible
        return collect();
    }
}

if (!function_exists('user_current_organization_name')) {
    function user_current_organization_name()
    {
        $org = user_current_organization();
        return $org?->display_name ?? $org?->legal_name ?? 'No organization';
    }
}
