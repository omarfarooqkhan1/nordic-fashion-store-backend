<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // <--- ADD THIS LINE
use Symfony\Component\HttpFoundation\Response; // Ensure this is also present if using Response type hint

class EnsureCartSessionOrAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If there's an authenticated user, Laravel's auth guards will handle it.
        // If not, ensure a session_id is present for guest carts.

        if (!$request->user() && !$request->header('X-Session-Id')) {
            // If no user and no session ID, generate one and set it in the request
            $sessionId = (string) Str::uuid(); // Generate a UUID
            $request->headers->set('X-Session-Id', $sessionId); // Set for current request

            // Pass the request to the next middleware/controller and get the response
            $response = $next($request);
            // Set the session ID in the response headers for the frontend to store
            $response->headers->set('X-Session-Id', $sessionId);
            return $response;
        }

        return $next($request);
    }
}