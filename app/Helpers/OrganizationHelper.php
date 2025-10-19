<?php
// app/Helpers/organisationHelper.php

use App\Models\Organisation;

if (!function_exists('current_organisation_id')) {
    /**
     * Get current organisation ID from session
     */
    function current_organisation_id()
    {
        return session('current_organisation_id', auth()->user()->organisation_id ?? null);
    }
}

if (!function_exists('current_organisation')) {
    /**
     * Get current organisation model
     */

    function current_organisation()
    {
        $id = current_organisation_id();
        return $id ? Organisation::find($id) : null;
    }
}

if (!function_exists('current_organisation_name')) {
    /**
     * Get current organisation name
     */
    function current_organisation_name()
    {
        return session('current_organisation_name', 'No organisation');
    }
}

if (!function_exists('can_access_organisation')) {
    /**
     * Check if current user can access an organisation
     */
    function can_access_organisation($organisationId)
    {
        return auth()->check() && auth()->user()->canAccessorganisation($organisationId);
    }
}

if (!function_exists('user_accessible_organisations')) {
    // Get all organisations current user can access
    function user_accessible_organisations()
    {
        if (!auth()->check()) {
            return collect();
        }

        return auth()->user()->accessibleorganisations();
    }
}

// American spelling variants for compatibility
if (!function_exists('current_organization_id')) {
    function current_organization_id()
    {
        return session('current_organization_id', auth()->user()?->organization_id);
    }
}

if (!function_exists('current_organization')) {
    function current_organization()
    {
        $id = current_organization_id();
        return $id ? Organisation::find($id) : null;
    }
}

if (!function_exists('current_organization_name')) {
    function current_organization_name()
    {
        return session('current_organization_name', 'No organization');
    }
}

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
            if ($sessionOrgId && \App\Models\Organisation::where('id', $sessionOrgId)->exists()) {
                return \App\Models\Organisation::find($sessionOrgId);
            }

            // 2. Check if Super Admin has any person affiliations (they might also be staff somewhere)
            if (isset($user->person_id) && $user->person_id) {
                $affiliation = \App\Models\PersonAffiliation::where('person_id', $user->person_id)
                    ->where('status', 'active')
                    ->with('organisation')
                    ->orderBy('start_date', 'desc')
                    ->first();

                if ($affiliation && $affiliation->organisation) {
                    // Store this as session for consistency
                    session(['current_organization_id' => $affiliation->organisation->id]);
                    return $affiliation->organisation;
                }
            }

            // 3. Fallback to first active organization
            $firstOrg = \App\Models\Organisation::where('is_active', true)->first() ?? \App\Models\Organisation::first();
            if ($firstOrg) {
                session(['current_organization_id' => $firstOrg->id]);
                return $firstOrg;
            }

            return null;
        }

        // For regular users - use database affiliations first, session as fallback
        // 1. Try to find active person affiliation (most reliable)
        if (isset($user->person_id) && $user->person_id) {
            $affiliation = \App\Models\PersonAffiliation::where('person_id', $user->person_id)
                ->where('status', 'active')
                ->with('organisation')
                ->orderBy('start_date', 'desc')
                ->first();

            if ($affiliation && $affiliation->organisation) {
                return $affiliation->organisation;
            }
        }

        // 2. Try session organization (if user switched context)
        $sessionOrgId = session('current_organization_id');
        if ($sessionOrgId) {
            $org = \App\Models\Organisation::find($sessionOrgId);
            if ($org) {
                return $org;
            }
        }

        // 3. Try direct organization relationship (if exists)
        if (isset($user->organisation_id) && $user->organisation_id) {
            $org = \App\Models\Organisation::find($user->organisation_id);
            if ($org) {
                return $org;
            }
        }

        // 4. Last fallback - check if user has any person record that links to organizations
        if (isset($user->person_id) && $user->person_id) {
            $anyAffiliation = \App\Models\PersonAffiliation::where('person_id', $user->person_id)
                ->with('organisation')
                ->orderBy('start_date', 'desc')
                ->first();

            if ($anyAffiliation && $anyAffiliation->organisation) {
                return $anyAffiliation->organisation;
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
        $organization = \App\Models\Organisation::find($organizationId);
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
                ->where('organisation_id', $organizationId)
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
            return \App\Models\Organisation::where('is_active', true)
                ->orderBy('display_name')
                ->get();
        }

        // For regular users, get organizations through person affiliations
        if (isset($user->person_id) && $user->person_id) {
            return \App\Models\Organisation::whereHas('affiliations', function($query) use ($user) {
                $query->where('person_id', $user->person_id)
                      ->where('status', 'active');
            })
            ->where('is_active', true)
            ->orderBy('display_name')
            ->get();
        }

        // Fallback: if user has direct organization relationship
        if (isset($user->organisation_id) && $user->organisation_id) {
            $org = \App\Models\Organisation::find($user->organisation_id);
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
