<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AllowAuthOrSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First, try to authenticate via Sanctum
        // This will set $request->user() if the token is valid
        $user = $request->user();
        
        // If user is authenticated via Sanctum, allow the request
        if ($user) {
            return $next($request);
        }
        
        // If not authenticated, check if guest user has session ID
        $sessionId = $request->header('X-Session-Id') ?? $request->input('session_id');
        if ($sessionId && strlen($sessionId) >= 20) {
            return $next($request);
        }
        
        // Neither authenticated nor has valid session ID
        return response()->json([
            'message' => 'Authentication required or valid session ID needed'
        ], 400);
    }
}
