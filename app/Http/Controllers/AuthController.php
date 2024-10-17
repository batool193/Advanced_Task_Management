<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\user\LoginRequest;

class AuthController extends Controller
{
    protected $authservice;
    /**
     * AuthController constructor
     *
     * @param AuthService $authservice
     */
    public function __construct(AuthService $authservice)
    {
        $this->authservice = $authservice;
    }

    /**
     * Log in existing user
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authservice->login($request->validated());
        if (!$result['success']) {
            return response()->json($result['message'], $result['status']);
        }
        return response()->json($result['data']);
    }
    /**
     * Log out the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $result = $this->authservice->logout();
        if (!$result['success']) {
            return response()->json($result['message'], $result['status']);
        }
        return response()->json($result['message']);
    }
}
