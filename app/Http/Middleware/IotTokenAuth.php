<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IotTokenAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Simple token check, in reality this could be stored in env or DB
        $validToken = env('IOT_NODE_TOKEN', 'hera-node-secret-string-123');
        
        $providedToken = $request->header('X-IoT-Token') ?? $request->bearerToken();

        if ($providedToken !== $validToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized IoT Node.'
            ], 401);
        }

        return $next($request);
    }
}
