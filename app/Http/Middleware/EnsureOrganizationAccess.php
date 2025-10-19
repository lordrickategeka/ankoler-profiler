<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureorganisationAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $currentOrgId = session('current_organisation_id');
        
        if (!$currentOrgId) {
            session()->flash('error', 'No organisation selected.');
            return redirect()->route('dashboard');
        }

        // Verify user has access to current organisation
        if (!auth()->user()->canAccessorganisation($currentOrgId)) {
            session()->flash('error', 'You do not have access to this organisation.');
            
            // Reset to user's primary organisation
            session([
                'current_organisation_id' => auth()->user()->organisation_id
            ]);
            
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
