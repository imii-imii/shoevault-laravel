<?php

if (!function_exists('userProfilePicture')) {
    /**
     * Get user profile picture URL with fallback to default
     *
     * @param \App\Models\User|null $user
     * @return string
     */
    function userProfilePicture($user = null)
    {
        if (!$user) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }
        
        if (!$user || !$user->profile_picture) {
            return asset('assets/images/profile.png');
        }
        
        $profilePicturePath = public_path($user->profile_picture);
        if (!file_exists($profilePicturePath)) {
            return asset('assets/images/profile.png');
        }
        
        return asset($user->profile_picture);
    }
}