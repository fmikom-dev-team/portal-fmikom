<?php

namespace App\Http\Middleware;

use App\Models\Audit\AuditApiRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
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
            $payload = Arr::except($request->input(), ['password', 'password_confirmation', 'token']);

            $user = $request->user();

            AuditApiRequest::create([
                'user_id' => $user?->id,
                'token_id' => $user && method_exists($user, 'currentAccessToken') ? $user->currentAccessToken()?->id : null,
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
