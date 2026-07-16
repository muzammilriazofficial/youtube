<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class GuestMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $session = Session::getInstance();

        if ($session->isAuthenticated()) {
            if ($request->expectsJson()) {
                return Response::error('You are already authenticated.', 403);
            }

            return Response::redirect(url('/dashboard'));
        }

        return $next($request);
    }
}
