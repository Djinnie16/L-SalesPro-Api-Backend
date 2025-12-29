<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only log successful API calls (2xx status)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $user = $request->user();
            
            // Don't log authentication requests (to avoid logging passwords)
            if (!str_contains($request->path(), 'auth/login')) {
                ActivityLog::create([
                    'user_id' => $user?->id,
                    'action' => $request->method(),
                    'model_type' => null,
                    'model_id' => null,
                    'description' => "API Access: {$request->method()} {$request->path()}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_data' => json_encode($request->except(['password', 'password_confirmation'])),
                ]);
            }
        }
        
        return $response;
    }
}