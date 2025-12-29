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
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        
        if (!$user || $user->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
                'errors' => ['role' => "Required role: {$role}"]
            ], 403);
        }
        
        return $next($request);
    }
}