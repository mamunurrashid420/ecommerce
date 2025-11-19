<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

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
        $token = $request->bearerToken();
        
        // If no token provided, return unauthenticated
        if (!$token) {
            return response()->json(['message' => 'Unauthenticated. No token provided.'], 401);
        }
        
        // Try to find the token
        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return response()->json(['message' => 'Unauthenticated. Invalid token.'], 401);
        }
        
        // Get the tokenable model (Customer or User)
        $user = $accessToken->tokenable;
        
        // Verify it's a Customer instance
        if (!$user || !($user instanceof Customer)) {
            $modelType = $user ? get_class($user) : 'null';
            Log::warning('CustomerAuth middleware: Token belongs to non-Customer model', [
                'token_id' => $accessToken->id,
                'tokenable_type' => $accessToken->tokenable_type,
                'tokenable_id' => $accessToken->tokenable_id,
                'resolved_model' => $modelType
            ]);
            
            return response()->json([
                'message' => 'Unauthenticated. Customer authentication required.',
                'error' => 'Token belongs to ' . $modelType . ', but Customer is required.'
            ], 401);
        }
        
        // Set the authenticated customer for the request
        auth()->setUser($user);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}

