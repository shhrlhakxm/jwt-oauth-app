<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class GoogleAuthController extends Controller
{
    // Step 1: Redirect user to Google
    public function redirect()
    {
        $url = $this->googleOAuth()
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    // Step 2: Google redirects back here with a code
    public function callback()
    {
        try {
            $googleUser = $this->googleOAuth()->stateless()->user();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid Google token'], 422);
        }

        // Find or create the user
        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(24)), // random password for OAuth users
            ]
        );

        $token = $this->jwtGuard()->login($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->jwtGuard()->getTTL() * 60,
            'user' => $user,
        ]);
    }

    protected function googleOAuth(): AbstractProvider
    {
        $provider = Socialite::driver('google');

        if (! $provider instanceof AbstractProvider) {
            throw new \RuntimeException('Google must use an OAuth2 Socialite provider.');
        }

        return $provider;
    }
}
