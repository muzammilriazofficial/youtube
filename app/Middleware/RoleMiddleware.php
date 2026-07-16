<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Role;
use App\Models\User;

class RoleMiddleware
{
    public function handle(Request $request, callable $next, string ...$roles): Response
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

        if (!empty($user['is_admin'])) {
            return $next($request);
        }

        $roleModel = new Role();
        $hasRole = $roleModel->userHasAnyRole($userId, $roles);

        if (!$hasRole) {
            return $request->expectsJson()
                ? Response::forbidden('You do not have the required role.')
                : Response::redirect('/dashboard')->withHeader('Location', '/dashboard');
        }

        return $next($request);
    }
}
