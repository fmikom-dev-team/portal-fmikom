<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class CustomCsrfMiddleware extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '__testing/*',
    ];

    /**
     * Create a new "XSRF-TOKEN" cookie.
     *
     * @param  Request  $request
     * @param  array  $config
     * @return Cookie
     */
    protected function newCookie($request, $config)
    {
        cookie()->queue(new Cookie(
            'XSRF-TOKEN',
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null
        ));

        return new Cookie(
            'fm_csrf',
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false, // must be false so Axios/Inertia can read it
            false,
            $config['same_site'] ?? null
        );
    }

    /**
     * Determine if the request has a valid CSRF token.
     *
     * @param  Request  $request
     * @return string|null
     */
    protected function getTokenFromRequest($request)
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if (! $token && $header = $request->header('X-XSRF-TOKEN')) {
            try {
                $token = $this->encrypter->decrypt($header, static::serialized());
            } catch (\Throwable $e) {
                // Fallback to raw unencrypted header string if cookie was excluded from encryption
                $token = $header;
            }
        }

        return $token;
    }
}
