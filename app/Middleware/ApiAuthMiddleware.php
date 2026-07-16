<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;

class ApiAuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return Response::unauthorized('Missing or invalid Authorization header. Use: Bearer <token>');
        }

        $hashedToken = hash('sha256', $token);

        $db = Database::getInstance();
        $record = $db->table('api_tokens')
            ->where('token', $hashedToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if ($record === null) {
            return Response::unauthorized('Invalid or expired API token.');
        }

        $userModel = new User();
        $user = $userModel->find((int) $record['user_id']);

        if ($user === null) {
            return Response::unauthorized('User associated with this token no longer exists.');
        }

        $request->merge(['_api_user' => $user]);
        $request->merge(['_api_token' => $record]);

        return $next($request);
    }
}
