<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class CustomerJWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next): Response
    {
        try {
            // Use the user guard
            Auth::shouldUse('customer');

            // Authenticate the token
            $user = Auth::guard('customer')->user();

            Log::info("Checking customer user authentication", ['user' => $user]);

            if (!$user) {
                return response()->json(['error' => 'Unauthorized - Admin not authenticated'], 401);
            }

            // Validate claims
            $claims = JWTAuth::parseToken()->getPayload();

            Log::info("Checking user claims", ['claims' => $claims->toArray()]);

            if ($claims->get('guard') !== 'customer' || $claims->get('role') !== 'customer') {
                return response()->json(['error' => 'Unauthorized - Invalid Role/Guard'], 403);
            }
        } catch (JWTException $e) {
            Log::error("JWT Exception occurred", ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Token is invalid or missing'], 401);
        }

        return $next($request);
    }
}
