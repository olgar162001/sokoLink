<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // make sure user has a role relation and name
        if (!$user->role || !in_array($user->role->name, $roles)) {
            return response()->json(['message' => 'Unauthoraized access'], 403);
        }

        return $next($request);
    }
}
