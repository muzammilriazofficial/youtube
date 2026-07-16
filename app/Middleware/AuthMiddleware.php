<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $session = Session::getInstance();

        if (!$session->isAuthenticated()) {
            if ($request->expectsJson()) {
                return Response::unauthorized('Please log in to continue.');
            }

            $session->flash('error', 'Please log in to continue.');
            return Response::redirect('/login');
        }

        $userModel = new \App\Models\User();
        $user = $userModel->find($session->getAuthUserId());

        if ($user === null) {
            $session->logout();
            $session->flash('error', 'Your account was not found.');
            return Response::redirect('/login');
        }

        $session->set('current_user', $user);

        return $next($request);
    }
}
