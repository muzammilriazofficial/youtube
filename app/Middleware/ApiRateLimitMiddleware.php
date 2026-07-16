<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class ApiRateLimitMiddleware
{
    private int $maxAttempts;

    private int $decayMinutes;

    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->maxAttempts   = $maxAttempts;
        $this->decayMinutes  = $decayMinutes;
    }

    public function handle(Request $request, callable $next): Response
    {
        $key = $this->resolveKey($request);

        $attempts    = $this->getAttempts($key);
        $isLimited   = $attempts >= $this->maxAttempts;
        $resetAt     = $this->getResetTimestamp($key);
        $remaining   = max(0, $this->maxAttempts - $attempts);

        if ($isLimited) {
            $response = Response::json([
                'status'  => 'error',
                'message' => 'Too many requests. Please try again later.',
                'data'    => null,
            ], 429);
        } else {
            $this->incrementAttempts($key);
            $remaining--;

            $response = $next($request);
        }

        if ($response instanceof Response) {
            $response->header('X-RateLimit-Limit', (string) $this->maxAttempts);
            $response->header('X-RateLimit-Remaining', (string) $remaining);
            $response->header('X-RateLimit-Reset', (string) $resetAt);
        }

        return $response;
    }

    private function resolveKey(Request $request): string
    {
        $userId = $request->input('_api_user.id');

        if ($userId !== null) {
            return 'api_rate_limit:user:' . $userId;
        }

        return 'api_rate_limit:ip:' . $request->ip();
    }

    private function getAttempts(string $key): int
    {
        $db = Database::getInstance();
        $row = $db->table('rate_limits')
            ->where('key', $key)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        return (int) ($row['attempts'] ?? 0);
    }

    private function getResetTimestamp(string $key): int
    {
        $db = Database::getInstance();
        $row = $db->table('rate_limits')
            ->where('key', $key)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if ($row === null) {
            return time() + ($this->decayMinutes * 60);
        }

        return strtotime((string) $row['expires_at']);
    }

    private function incrementAttempts(string $key): void
    {
        $db = Database::getInstance();
        $existing = $db->table('rate_limits')
            ->where('key', $key)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->decayMinutes} minutes"));

        if ($existing !== null) {
            $db->table('rate_limits')
                ->where('key', $key)
                ->update([
                    'attempts'   => $existing['attempts'] + 1,
                    'expires_at' => $expiresAt,
                ]);
        } else {
            $db->table('rate_limits')->insert([
                'key'        => $key,
                'attempts'   => 1,
                'expires_at' => $expiresAt,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public static function cleanup(): void
    {
        $db = Database::getInstance();
        $db->raw("DELETE FROM rate_limits WHERE expires_at < NOW()");
    }
}
