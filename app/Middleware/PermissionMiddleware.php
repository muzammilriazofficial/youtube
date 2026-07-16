<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Permission;
use App\Models\User;

class PermissionMiddleware
{
    public function handle(Request $request, callable $next, string ...$permissions): Response
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

        $permModel = new Permission();
        $hasPermission = $permModel->userHasAnyPermission($userId, $permissions);

        if (!$hasPermission) {
            return $request->expectsJson()
                ? Response::forbidden('You do not have the required permission.')
                : Response::redirect('/dashboard');
        }

        return $next($request);
    }
}
