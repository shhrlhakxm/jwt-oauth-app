<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class GoogleAuthController extends Controller
{
    /**
     * Browser: redirect to Google. API (Accept: application/json): return JSON with URL.
     */
    public function redirect(Request $request)
    {
        $redirect = $this->googleOAuth()->stateless()->redirect();

        if ($request->wantsJson()) {
            return response()->json(['url' => $redirect->getTargetUrl()]);
        }

        return $redirect;
    }

    /**
     * Browser: redirect to dashboard with token in session flash. API: JSON body.
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = $this->googleOAuth()->stateless()->user();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Invalid Google token'], 422);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Google sign-in failed. Try again.');
        }

        $user = User::updateOrCreate(
            ['google_id' => $googleUser->getId()],
            [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(24)),
            ]
        );

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
