<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Rate limiting service using database-backed token bucket algorithm.
 */
class RateLimiter
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Check if a rate limit has been exceeded.
     * Returns true if the attempt is allowed, false if rate limited.
     */
    public function attempt(string $key, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        $key        = $this->normalizeKey($key);
        $decaySeconds = $decayMinutes * 60;
        $now        = time();

        $record = $this->db->table('rate_limits')
            ->where('key', $key)
            ->first();

        if ($record === null) {
            $this->db->table('rate_limits')->insert([
                'key'        => $key,
                'attempts'   => 1,
                'last_attempt_at' => date('Y-m-d H:i:s', $now),
                'created_at' => date('Y-m-d H:i:s', $now),
            ]);
            return true;
        }

        $lastAttempt = strtotime($record['last_attempt_at']);
        $elapsed     = $now - $lastAttempt;

        if ($elapsed >= $decaySeconds) {
            $this->db->table('rate_limits')
                ->where('key', $key)
                ->update([
                    'attempts'         => 1,
                    'last_attempt_at'  => date('Y-m-d H:i:s', $now),
                ]);
            return true;
        }

        if ((int) $record['attempts'] >= $maxAttempts) {
            return false;
        }

        $this->db->table('rate_limits')
            ->where('key', $key)
            ->update([
                'attempts'         => (int) $record['attempts'] + 1,
                'last_attempt_at'  => date('Y-m-d H:i:s', $now),
            ]);

        return true;
    }

    /**
     * Get the number of remaining attempts.
     */
    public function remaining(string $key, int $maxAttempts = 60, int $decayMinutes = 1): int
    {
        $key          = $this->normalizeKey($key);
        $decaySeconds = $decayMinutes * 60;
        $now          = time();

        $record = $this->db->table('rate_limits')
            ->where('key', $key)
            ->first();

        if ($record === null) {
            return $maxAttempts;
        }

        $lastAttempt = strtotime($record['last_attempt_at']);
        $elapsed     = $now - $lastAttempt;

        if ($elapsed >= $decaySeconds) {
            return $maxAttempts;
        }

        return max(0, $maxAttempts - (int) $record['attempts']);
    }

    /**
     * Get the number of seconds until the rate limit resets.
     */
    public function retryAfter(string $key, int $decayMinutes = 1): int
    {
        $key          = $this->normalizeKey($key);
        $decaySeconds = $decayMinutes * 60;

        $record = $this->db->table('rate_limits')
            ->where('key', $key)
            ->first();

        if ($record === null) {
            return 0;
        }

        $lastAttempt = strtotime($record['last_attempt_at']);
        $elapsed     = time() - $lastAttempt;

        return max(0, $decaySeconds - $elapsed);
    }

    /**
     * Clear the rate limit for a key.
     */
    public function clear(string $key): bool
    {
        $key = $this->normalizeKey($key);

        $affected = $this->db->table('rate_limits')
            ->where('key', $key)
            ->delete();

        return true;
    }

    /**
     * Check if a key is currently rate limited (without incrementing).
     */
    public function isRateLimited(string $key, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        return $this->remaining($key, $maxAttempts, $decayMinutes) <= 0;
    }

    /**
     * Clean up expired rate limit records.
     */
    public function cleanup(int $olderThanMinutes = 60): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($olderThanMinutes * 60));

        $affected = $this->db->table('rate_limits')
            ->where('last_attempt_at', '<', $cutoff)
            ->delete();

        return $affected;
    }

    /**
     * Normalize a rate limit key for storage.
     */
    private function normalizeKey(string $key): string
    {
        return 'rate_limit:' . preg_replace('/[^a-zA-Z0-9_\-:.]/', '_', $key);
    }
}
