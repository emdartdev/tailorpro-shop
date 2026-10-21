<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $flattened = [];
        foreach ($roles as $r) {
            foreach (explode(',', $r) as $sub) {
                if ($sub !== '') {
                    $flattened[] = trim($sub);
                }
            }
        }

        if (! empty($flattened) && ! $user->hasRole($flattened)) {
            return response()->json(['error' => 'Unauthorized access: insufficient privileges.'], 403);
        }

        return $next($request);
    }
}
