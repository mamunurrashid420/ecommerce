<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Customer;

class CustomerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = null;
        
        // First, try to get user from request (Sanctum might have already resolved it)
        $user = $request->user();
        
        // If that doesn't work, try auth()->user()
        if (!$user) {
            $user = auth()->user();
        }
        
        // If still no user, manually resolve from token
        if (!$user) {
            $token = $request->bearerToken();
            
            if ($token) {
                $accessToken = PersonalAccessToken::findToken($token);
                
                if ($accessToken) {
                    $user = $accessToken->tokenable;
                }
            }
        }
        
        // Verify it's a Customer instance
        if (!$user || !($user instanceof Customer)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // Set the authenticated customer for the request
        auth()->setUser($user);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}

