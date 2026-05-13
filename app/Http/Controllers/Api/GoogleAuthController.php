<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Browser: redirect to Google. API (Accept: application/json): JSON with authorization URL.
     */
    public function redirect(Request $request)
    {
        $response = $this->googleOAuth()->redirect();

        if ($request->wantsJson()) {
            return response()->json(['url' => $response->getTargetUrl()]);
        }

        return $response;
    }

    /**
     * Browser: redirect to dashboard with JWT in session flash (dashboard stores it in localStorage).
     * API: JSON body with access_token and user.
     */
    public function callback(Request $request)
    {
        try {
            $provider = $this->googleOAuth();
            $googleUser = $request->wantsJson()
                ? $provider->stateless()->user()
                : $provider->user();

            if (! $googleUser->getEmail()) {
                throw new \RuntimeException('Google did not return an email.');
            }

            $user = $this->resolveUserFromGoogle($googleUser);
            $token = $this->jwtGuard()->login($user);

            if ($request->wantsJson()) {
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => $this->jwtGuard()->getTTL() * 60,
                    'user' => $user,
                ]);
            }

            return redirect()
                ->route('dashboard')
                ->with('access_token', $token);
        } catch (Throwable $e) {
            Log::error('Google OAuth failed', [
                'message' => $e->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Invalid Google token'], 422);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Google sign-in failed. Try again.');
        }
    }

    protected function resolveUserFromGoogle(SocialiteUser $googleUser): User
    {
        $googleId = (string) $googleUser->getId();
        $email = $googleUser->getEmail();

        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            $user->update([
                'name' => $googleUser->getName() ?: $user->name,
                'email' => $email,
            ]);

            return $user->fresh();
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            if ($existing->google_id !== null && $existing->google_id !== $googleId) {
                throw new \RuntimeException('Email already linked to a different Google account.');
            }

            $existing->update([
                'google_id' => $googleId,
                'name' => $googleUser->getName() ?: $existing->name,
            ]);

            return $existing->fresh();
        }

        return User::create([
            'google_id' => $googleId,
            'name' => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'User'),
            'email' => $email,
            'password' => Hash::make(Str::random(24)),
        ]);
    }

    protected function googleOAuth(): AbstractProvider
    {
        $provider = Socialite::driver('google');

        if (! $provider instanceof AbstractProvider) {
            throw new \RuntimeException('Google must use an OAuth2 Socialite provider.');
        }

        $provider->setScopes(['openid', 'profile', 'email']);

        return $provider;
    }
}
