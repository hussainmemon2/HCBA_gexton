<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class ApiAuthMiddleware
{
 public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'message' => 'Authorization token missing.',
            ], 401);
        }

        $tokenString = substr($header, 7);

        $accessToken = PersonalAccessToken::findToken($tokenString);

        if (!$accessToken) {
            return response()->json([
                'message' => 'Invalid or expired token.',
            ], 401);
        }
        // Attach user to request
        $request->setUserResolver(function () use ($accessToken) {
            return $accessToken->tokenable;
        });
        return $next($request);
    }
}
