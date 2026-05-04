<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        return $this->respondWithToken($guard->refresh());
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token)
    {
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        
        $user = $guard->user() ?? $guard->setToken($token)->user();

        // Standard token is used as access_token (15 mins TTL)
        $accessToken = $guard->setTTL(15)->tokenById($user->user_id);

        // We generate a separate token with 6 hours (360 mins) TTL for refresh token
        // Also adding a custom claim 'type' => 'refresh' to distinguish it if needed later
        $refreshToken = $guard->setTTL(360)->claims(['type' => 'refresh'])->tokenById($user->user_id);

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60, // returns TTL of last generated token (or default)
            'user' => $user
        ]);
    }
}
