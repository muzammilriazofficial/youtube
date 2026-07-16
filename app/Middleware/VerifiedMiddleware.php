<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

class VerifiedMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $session = Session::getInstance();

        if (!$session->isAuthenticated()) {
            return $request->expectsJson()
                ? Response::unauthorized('Please log in.')
                : Response::redirect('/login');
        }

        $userId = $session->getAuthUserId();
        $userModel = new User();
        $user = $userModel->find($userId);

        if ($user === null) {
            $session->logout();
            return Response::redirect('/login');
        }

        if (empty($user['is_verified'])) {
            if ($request->expectsJson()) {
                return Response::forbidden('Please verify your email address.');
            }

            $session->flash('warning', 'Please verify your email address before continuing.');
            return Response::redirect('/verify-email');
        }

        return $next($request);
    }
}
