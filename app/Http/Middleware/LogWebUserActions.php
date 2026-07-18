<?php

namespace App\Http\Middleware;

use App\Modules\WorkOs\Services\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogWebUserActions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log modification actions (POST, PUT, PATCH, DELETE) for authenticated users
        if (auth()->check() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $path = $request->path();

            // Skip audit log operations to prevent recursion/noise
            if (str_contains($path, 'audit-logs') || str_contains($path, 'flush-cache')) {
                return $response;
            }

            $method = $request->method();
            $statusCode = $response->getStatusCode();

            // Build event code based on route scope
            $action = 'user.modify';
            if (str_starts_with($path, 'portal-admin')) {
                $action = 'portal.'.strtolower($method);
            } elseif (str_starts_with($path, 'pagi')) {
                $action = 'pagi.'.strtolower($method);
            } elseif (str_starts_with($path, 'workos')) {
                $action = 'workos.'.strtolower($method);
            } elseif (str_starts_with($path, 'fast')) {
                $action = 'fast.'.strtolower($method);
            } elseif (str_starts_with($path, 'trace')) {
                $action = 'trace.'.strtolower($method);
            }

            $metadata = [
                'path' => $path,
                'method' => $method,
                'status_code' => $statusCode,
                'payload' => \Illuminate\Support\Arr::except($request->input(), ['password', 'password_confirmation', 'token', '_token', '_method']),
            ];

            try {
                AuditLogger::log($action, 'info', $metadata);
            } catch (\Throwable $e) {
                // Silent catch to prevent request disruptions
            }
        }

        return $response;
    }
}
