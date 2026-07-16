<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;

class CookieService
{
    private static ?self $instance = null;

    private string $prefix = 'yt_';

    private int $defaultExpiry = 0;

    private string $path = '/';

    private bool $secure = false;

    private bool $httponly = true;

    private string $samesite = 'Lax';

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set(string $name, string $value, int $expiry = 0, array $options = []): bool
    {
        $name     = $this->prefix . $name;
        $expiry   = $expiry > 0 ? time() + $expiry : $this->defaultExpiry;
        $path     = $options['path'] ?? $this->path;
        $secure   = $options['secure'] ?? $this->secure;
        $httponly  = $options['httponly'] ?? $this->httponly;
        $samesite = $options['samesite'] ?? $this->samesite;
        $domain   = $options['domain'] ?? '';

        $params = [
            'expires'  => $expiry,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly'  => $httponly,
            'samesite' => $samesite,
        ];

        return setcookie($name, $value, $params);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        $name = $this->prefix . $name;
        return $_COOKIE[$name] ?? $default;
    }

    public function delete(string $name): bool
    {
        return $this->set($name, '', -3600);
    }

    public function has(string $name): bool
    {
        return $this->get($name) !== null;
    }

    public function remember(string $key, string $value, int $days = 30): bool
    {
        $token = $this->generateRememberToken($value, $key);

        $this->set("remember_{$key}", $token, $days * 86400, [
            'httponly' => true,
            'secure'  => $this->secure,
            'samesite' => 'Lax',
        ]);

        $this->set("remember_{$key}_user", (string) $value, $days * 86400, [
            'httponly' => true,
            'secure'  => $this->secure,
            'samesite' => 'Lax',
        ]);

        return true;
    }

    public function getRememberToken(): ?string
    {
        $keys = array_filter(array_keys($_COOKIE), fn($k) => str_starts_with($k, $this->prefix . 'remember_'));

        foreach ($keys as $key) {
            $cleanKey = str_replace($this->prefix . 'remember_', '', $key);
            if (str_ends_with($cleanKey, '_user')) {
                continue;
            }
            return $cleanKey;
        }

        return null;
    }

    public function validateRememberToken(string $key, string $token): ?int
    {
        $storedToken = $this->get("remember_{$key}");
        $userId      = $this->get("remember_{$key}_user");

        if ($storedToken === null || $userId === null) {
            return null;
        }

        if (!hash_equals($storedToken, $token)) {
            return null;
        }

        return (int) $userId;
    }

    public function clearRemember(string $key): void
    {
        $this->delete("remember_{$key}");
        $this->delete("remember_{$key}_user");
    }

    public function flush(): void
    {
        foreach ($_COOKIE as $name => $value) {
            if (str_starts_with($name, $this->prefix)) {
                $this->delete(substr($name, strlen($this->prefix)));
            }
        }
    }

    private function generateRememberToken(int $userId, string $key): string
    {
        $payload = $userId . '|' . $key . '|' . time();
        return hash_hmac('sha256', $payload, bin2hex(random_bytes(32)));
    }
}
