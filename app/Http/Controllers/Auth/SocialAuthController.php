<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        // Placeholder for Google OAuth. Requires Laravel Socialite and Google credentials.
        // If Socialite is not installed/configured, provide a friendly message.
        if (!class_exists('Laravel\\Socialite\\Facades\\Socialite') || empty(config('services.google.client_id'))) {
            return redirect()->back()->with('error', 'Google Sign-In is not configured yet. Please contact the administrator.');
        }
        // return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
        return redirect()->back()->with('error', 'Google Sign-In is temporarily unavailable.');
    }

    public function callback(Request $request)
    {
        // Placeholder for Google OAuth callback.
        return redirect()->route('customer.login')->with('error', 'Google Sign-In is not configured yet.');
    }
}