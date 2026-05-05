<?php

namespace App\Services;

use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * Get the guard instance.
     * 
     * @return JWTGuard
     */
    protected function getGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');
        return $guard;
    }

    /**
     * Handle user login.
     * 
     * @param array $credentials
     * @return array|null
     */
    public function login(array $credentials): ?array
    {
        $guard = $this->getGuard();
        
        if (!$token = $guard->attempt($credentials)) {
            return null;
        }

        return $this->formatTokenResponse($token);
    }

    /**
     * Handle token refresh.
     * 
     * @return array
     */
    public function refresh(): array
    {
        $guard = $this->getGuard();
        $token = $guard->refresh();
        
        return $this->formatTokenResponse($token);
    }

    /**
     * Handle user logout.
     * 
     * @return void
     */
    public function logout(): void
    {
        $this->getGuard()->logout();
    }

    /**
     * Format the token response structure.
     * 
     * @param string $token
     * @return array
     */
    protected function formatTokenResponse(string $token): array
    {
        $guard = $this->getGuard();
        
        // Ensure user is retrieved (either from current session or by token)
        $user = $guard->user() ?? $guard->setToken($token)->user();

        // Standard token used as access_token (15 mins TTL)
        $accessToken = $guard->setTTL(15)->tokenById($user->user_id);

        // Separate token with 7 hours (420 mins) TTL for refresh token
        $refreshToken = $guard->setTTL(420)->claims(['type' => 'refresh'])->tokenById($user->user_id);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => 15 * 60, // 15 minutes in seconds
            'user' => $user
        ];
    }
}
