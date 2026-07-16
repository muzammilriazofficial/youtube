<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    private array $exceptMethods = ['GET', 'HEAD', 'OPTIONS'];

    private array $exceptPaths = [];

    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), $this->exceptMethods, true)) {
            return $next($request);
        }

        $path = $request->path();

        foreach ($this->exceptPaths as $exceptPath) {
            if (str_starts_with($path, $exceptPath)) {
                return $next($request);
            }
        }

        $token = $request->input('_token', '');

        if ($token === '') {
            $token = $request->header('X-CSRF-Token', '');
        }

        if ($token === '') {
            $token = $request->bearerToken() ?? '';
        }

        $session = Session::getInstance();
        $sessionToken = $session->get('csrf_token');

        if ($sessionToken === null || $sessionToken === '' || $token === '') {
            if ($request->expectsJson()) {
                return Response::error('CSRF token missing.', 419);
            }

            return new Response('CSRF token mismatch.', 419);
        }

        if (!hash_equals($sessionToken, $token)) {
            if ($request->expectsJson()) {
                return Response::error('CSRF token mismatch.', 419);
            }

            return new Response('CSRF token mismatch.', 419);
        }

        return $next($request);
    }

    public function except(array $paths): self
    {
        $clone = clone $this;
        $clone->exceptPaths = array_merge($clone->exceptPaths, $paths);
        return $clone;
    }
}
