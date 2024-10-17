<?php

namespace App\Services;

use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * AuthService
 *
 * This service handles user authentication, including login, and logout.
 */
class AuthService
{
    /**
     * Authenticate a user and generate a JWT token.
     *
     * @param array $validateddata The validated user data.
     * @return array The response containing the user and JWT token or an error message.
     */

    public static function login($validatedData)
    {
        if (!$token = JWTAuth::attempt($validatedData))
                return [
                    'success' => false,
                    'message' => 'Unauthorized',
                    'status' => 401,
                ];
            $user = JWTAuth::user();
            return [
                'success' => true,
                'data' => [
                    'user' => $user,
                    'authorization' => [
                        'token' => $token,
                        'type' => 'bearer'
                    ]
                ]
            ];
    }

    /**
     * Logout the authenticated user.
     *
     * @return array The response indicating successful logout.
     */
    public static function logout()
    {
        if (JWTAuth::invalidate(JWTAuth::getToken()))
            return [
                'success' => true,
                'message' => 'Successfully logged out',
            ];

        return [
            'success' => false,
            'message' => 'Failed to logout, please try again',
            'status' => 500,
        ];
    }
}
