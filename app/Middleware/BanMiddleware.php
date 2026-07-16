<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

class BanMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $session = Session::getInstance();

        if (!$session->isAuthenticated()) {
            return $next($request);
        }

        $userId = $session->getAuthUserId();
        $userModel = new User();
        $user = $userModel->find($userId);

        if ($user === null) {
            $session->logout();
            return Response::redirect('/login');
        }

        if (!empty($user['is_banned'])) {
            $session->logout();

            if ($request->expectsJson()) {
                return Response::forbidden('Your account has been suspended.');
            }

            $session->flash('error', 'Your account has been suspended. Please contact support.');
            return Response::redirect('/account-suspended');
        }

        return $next($request);
    }
}
