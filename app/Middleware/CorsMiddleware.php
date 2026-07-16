<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class CorsMiddleware
{
    private array $allowedOrigins = [
        '*',
    ];

    private array $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ];

    private array $allowedHeaders = [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
        'Origin',
        'X-CSRF-Token',
        'X-HTTP-Method-Override',
    ];

    private bool $allowCredentials = true;

    private int $maxAge = 86400;

    public function handle(Request $request, callable $next): Response
    {
        $origin = $request->header('Origin', '*');

        if (in_array('*', $this->allowedOrigins, true)) {
            $allowOrigin = '*';
        } elseif (in_array($origin, $this->allowedOrigins, true)) {
            $allowOrigin = $origin;
        } else {
            $allowOrigin = $this->allowedOrigins[0] ?? '*';
        }

        $corsHeaders = [
            'Access-Control-Allow-Origin'      => $allowOrigin,
            'Access-Control-Allow-Methods'     => implode(', ', $this->allowedMethods),
            'Access-Control-Allow-Headers'     => implode(', ', $this->allowedHeaders),
            'Access-Control-Max-Age'           => (string) $this->maxAge,
            'Access-Control-Expose-Headers'    => 'X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset, Content-Disposition',
            'Vary'                             => 'Origin',
        ];

        if ($this->allowCredentials && $allowOrigin !== '*') {
            $corsHeaders['Access-Control-Allow-Credentials'] = 'true';
        }

        if ($request->method() === 'OPTIONS') {
            $response = new Response('', 204);
            foreach ($corsHeaders as $name => $value) {
                $response->header($name, $value);
            }
            return $response;
        }

        $response = $next($request);

        if ($response instanceof Response) {
            foreach ($corsHeaders as $name => $value) {
                $response->header($name, $value);
            }
        }

        return $response;
    }
}
