<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    private static ?self $instance = null;

    private bool $started = false;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $config = $this->getConfig();

        session_name($config['name'] ?? 'yt_session');

        $lifetime = ($config['lifetime'] ?? 120) * 60;

        $params = session_get_cookie_params();
        $params['lifetime'] = $lifetime;
        $params['path']     = '/';
        $params['secure']   = $config['secure'] ?? false;
        $params['httponly']  = $config['httponly'] ?? true;
        $params['samesite'] = $config['same_site'] ?? 'Lax';

        session_set_cookie_params($params);

        session_set_save_handler(
            new \SessionHandler(),
            true
        );

        session_start();
        $this->started = true;

        $this->regenerateIfNeeded();
    }

    public function all(): array
    {
        return $_SESSION;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function put(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function exists(string $key): bool
    {
        return $this->has($key);
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        $this->forget($key);
    }

    public function clear(): void
    {
        $_SESSION = [];
    }

    public function flush(): void
    {
        $this->clear();
    }

    public function increment(string $key, int $amount = 1): void
    {
        $_SESSION[$key] = ($this->get($key, 0)) + $amount;
    }

    public function decrement(string $key, int $amount = 1): void
    {
        $this->increment($key, -$amount);
    }

    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;

        $this->removeFlash($key);

        return $value;
    }

    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    public function removeFlash(string $key): void
    {
        unset($_SESSION['_flash'][$key]);
    }

    public function flashAll(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    public function now(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function reflash(): void
    {
        $_SESSION['_reflash'] = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
    }

    public function keep(array $keys): void
    {
        foreach ($keys as $key) {
            if (isset($_SESSION['_flash'][$key])) {
                $_SESSION['_reflash'][$key] = $_SESSION['_flash'][$key];
            }
        }
        unset($_SESSION['_flash']);
    }

    public function setAuthenticated(int $userId): void
    {
        $this->set('user_id', $userId);
        $this->set('auth_token', bin2hex(random_bytes(32)));
        $this->regenerate();
    }

    public function getAuthUserId(): ?int
    {
        $id = $this->get('user_id');
        return $id !== null ? (int) $id : null;
    }

    public function isAuthenticated(): bool
    {
        return $this->has('user_id');
    }

    public function logout(): void
    {
        $this->forget('user_id');
        $this->forget('auth_token');
        $this->regenerate();
    }

    public function regenerate(bool $deleteOld = false): void
    {
        session_regenerate_id($deleteOld);
    }

    public function id(): string
    {
        return session_id();
    }

    public function invalidate(): void
    {
        session_destroy();
        $this->started = false;
    }

    public function token(): string
    {
        if (!$this->has('csrf_token')) {
            $this->set('csrf_token', bin2hex(random_bytes(32)));
        }
        return $this->get('csrf_token');
    }

    public function validateToken(string $token): bool
    {
        $stored = $this->get('csrf_token');
        if ($stored === null || $token === '') {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public function isStarted(): bool
    {
        return $this->started || session_status() === PHP_SESSION_ACTIVE;
    }

    private function getConfig(): array
    {
        $configPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

        if (file_exists($configPath)) {
            $config = require $configPath;
            return $config['session'] ?? [];
        }

        return [
            'lifetime'  => 120,
            'name'      => 'yt_session',
            'secure'    => false,
            'httponly'   => true,
            'same_site' => 'Lax',
        ];
    }

    private function regenerateIfNeeded(): void
    {
        $lastActivity = $this->get('_last_activity', 0);
        $lifetime     = ($this->getConfig()['lifetime'] ?? 120) * 60;

        if (time() - $lastActivity > $lifetime) {
            $this->regenerate(true);
        }

        $this->set('_last_activity', time());
    }
}
