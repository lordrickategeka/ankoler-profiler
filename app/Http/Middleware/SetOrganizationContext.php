<?php
// app/Http/Middleware/SetorganisationContext.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organisation;
use Symfony\Component\HttpFoundation\Response;

class SetorganisationContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // If no organisation in session, set user's primary org
            if (!session()->has('current_organisation_id')) {
                $this->setDefaultorganisation($user);
            }
            
            // Verify user still has access to current organisation
            $currentOrgId = session('current_organisation_id');
            if ($currentOrgId && !$user->canAccessorganisation($currentOrgId)) {
                // User lost access, reset to primary organisation
                $this->setDefaultorganisation($user);
            }
            
            // Store organisation details for easy access
            if (session('current_organisation_id')) {
                $org = Organisation::find(session('current_organisation_id'));
                if ($org) {
                    session([
                        'current_organisation_name' => $org->display_name,
                        'current_organisation_code' => $org->code,
                        'current_organisation_logo' => $org->logo_path,
                    ]);
                    
                    // Share with all views
                    view()->share('currentorganisation', $org);
                }
            }
        }
        
        return $next($request);
    }

    /**
     * Set user's default/primary organisation
     */
    private function setDefaultorganisation($user)
    {
        $defaultOrg = $user->organisation_id;
        
        // If no primary org set, get first accessible organisation
        if (!$defaultOrg) {
            $accessible = $user->accessibleorganisations();
            $defaultOrg = $accessible->first()?->id;
        }
        
        if ($defaultOrg) {
            session([
                'current_organisation_id' => $defaultOrg
            ]);
        }
    }
}
