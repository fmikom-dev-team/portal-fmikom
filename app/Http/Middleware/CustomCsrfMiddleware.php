<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class CustomCsrfMiddleware extends Middleware
{
    /**
     * Create a new "XSRF-TOKEN" cookie.
     *
     * @param  Request  $request
     * @param  array  $config
     * @return Cookie
     */
    protected function newCookie($request, $config)
    {
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
}
