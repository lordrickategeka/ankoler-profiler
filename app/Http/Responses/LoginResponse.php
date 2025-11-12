<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    /**
     * Where to redirect users after login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toResponse($request)
    {
        $user = Auth::user();
        // Check if user has the 'Person' role
    if ($user && $user->hasRole('Person')) {
            return redirect()->route('persons.profile-current');
        }
        // Default Jetstream redirect
        return redirect()->intended(config('fortify.home'));
    }
}
