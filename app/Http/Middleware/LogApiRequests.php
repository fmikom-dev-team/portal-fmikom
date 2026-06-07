<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Audit\AuditApiRequest;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        $response = $next($request);

        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000);

        // Only log API routes
        if ($request->is('api/*')) {
            // Strip sensitive data from payload
            $payload = $request->except(['password', 'password_confirmation', 'token']);

            AuditApiRequest::create([
                'user_id' => auth()->id(),
                'token_id' => method_exists(auth()->user(), 'currentAccessToken') ? auth()->user()->currentAccessToken()?->id : null,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'response_time_ms' => $executionTime,
                'request_payload' => $payload,
                'ip_address' => $request->ip(),
            ]);
        }

        return $response;
    }
}
