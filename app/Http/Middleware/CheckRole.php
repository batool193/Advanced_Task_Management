<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * This middleware is used to restrict access to certain resources
     * to certain roles. The role is passed as a parameter to the middleware
     * and must match the role of the authenticated user.
     * If the role does not match, the middleware returns a 403 response.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next,string $role): Response
    {
        /** @var \App\Models\User $user */
        $user = JWTAuth::user();

        if ($user && ($user->role == $role)) {
        return $next($request);

        }

         // If the role does not match, return a 403 response.
         return response('Unauthorized', 403);
    }
    }

