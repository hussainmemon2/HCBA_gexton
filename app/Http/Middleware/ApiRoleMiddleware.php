<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ($user->status === 'suspended') {
            return response()->json(['message' => 'Your account is suspended.'], 403);
        }
        if ($user->status === 'inactive') {
            return response()->json(['message' => 'Your account is inactive please contact to Administrator.'], 403);
        }

        if (!in_array($user->role, $roles)) {
            return response()->json(['message' => 'Forbidden. You do not have access to this resource.'], 403);
        }

        return $next($request);
    }
}
