<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class RateLimitMiddleware
{
    private int $maxAttempts;

    private int $decayMinutes;

    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function handle(Request $request, callable $next): Response
    {
        $key = $this->resolveKey($request);

        if ($this->isRateLimited($key)) {
            if ($request->expectsJson()) {
                return Response::error('Too many requests. Please try again later.', 429);
            }

            http_response_code(429);
            return new Response('Too many requests. Please try again later.', 429);
        }

        $this->incrementAttempts($key);

        $response = $next($request);

        $response->header('X-RateLimit-Limit', (string) $this->maxAttempts);
        $response->header('X-RateLimit-Remaining', (string) max(0, $this->maxAttempts - $this->getAttempts($key)));

        return $response;
    }

    private function resolveKey(Request $request): string
    {
        return 'rate_limit:' . $request->ip();
    }

    private function isRateLimited(string $key): bool
    {
        return $this->getAttempts($key) >= $this->maxAttempts;
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

    private function incrementAttempts(string $key): void
    {
        $db = Database::getInstance();
        $existing = $db->table('rate_limits')
            ->where('key', $key)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->decayMinutes} minutes"));

        if ($existing) {
            $db->table('rate_limits')
                ->where('key', $key)
                ->update([
                    'attempts' => $existing['attempts'] + 1,
                    'expires_at' => $expiresAt,
                ]);
        } else {
            $db->table('rate_limits')->insert([
                'key' => $key,
                'attempts' => 1,
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
