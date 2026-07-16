<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $query = [];

    private array $body = [];

    private array $server = [];

    private array $files = [];

    private array $headers = [];

    private ?array $jsonBody = null;

    private static ?self $instance = null;

    public function __construct(array $query = [], array $body = [], array $server = [], array $files = [])
    {
        $this->query  = $query ?: $_GET;
        $this->body   = $body ?: $_POST;
        $this->server = $server ?: $_SERVER;
        $this->files  = $files ?: $_FILES;

        $this->parseHeaders();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function capture(): self
    {
        return self::getInstance();
    }

    public function method(): string
    {
        $method = $this->server('REQUEST_METHOD', 'GET');

        if (strtoupper($method) === 'POST') {
            $override = $this->input('_method');

            if ($override !== null) {
                return strtoupper($override);
            }

            $headerOverride = $this->header('X-HTTP-Method-Override');
            if ($headerOverride !== null) {
                return strtoupper($headerOverride);
            }
        }

        return strtoupper($method);
    }

    public function path(): string
    {
        $uri = $this->server('REQUEST_URI', '/');

        $path = parse_url($uri, PHP_URL_PATH);

        if ($path === false || $path === null) {
            return '/';
        }

        $path = rawurldecode($path);

        $scriptName = $this->server('SCRIPT_NAME', '');
        if ($scriptName !== '') {
            $publicPos = strpos($scriptName, '/public/');
            if ($publicPos !== false) {
                $basePath = substr($scriptName, 0, $publicPos);
            } else {
                $basePath = rtrim(dirname($scriptName), '\\/');
            }
            if ($basePath !== '' && $basePath !== '.' && str_starts_with($path, $basePath)) {
                $path = substr($path, strlen($basePath));
            }
        }

        return $path === '' ? '/' : $path;
    }

    public function url(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host   = $this->host();
        $path   = $this->path();

        return $scheme . '://' . $host . $path;
    }

    public function fullUrl(): string
    {
        $url  = $this->url();
        $query = $this->server('QUERY_STRING', '');

        if ($query !== '') {
            $url .= '?' . $query;
        }

        return $url;
    }

    public function host(): string
    {
        return $this->server('HTTP_HOST', 'localhost');
    }

    public function port(): int
    {
        return (int) $this->server('SERVER_PORT', 80);
    }

    public function isSecure(): bool
    {
        return (
            (!empty($this->server('HTTPS')) && $this->server('HTTPS') !== 'off')
            || $this->server('SERVER_PORT', 80) == 443
            || $this->header('X-Forwarded-Proto', '') === 'https'
        );
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $value = $this->query[$key] ?? $this->body[$key] ?? null;
        return $value !== null ? $value : $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function only(array $keys): array
    {
        $all    = $this->all();
        $result = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $all)) {
                $result[$key] = $all[$key];
            }
        }

        return $result;
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->query) || array_key_exists($key, $this->body);
    }

    public function missing(string $key): bool
    {
        return !$this->has($key);
    }

    public function filled(string $key): bool
    {
        $value = $this->input($key);
        return $value !== null && $value !== '';
    }

    public function json(string $key = '', mixed $default = null): mixed
    {
        if ($this->jsonBody === null) {
            $raw = file_get_contents('php://input');
            $this->jsonBody = json_decode($raw ?? '', true) ?? [];
        }

        if ($key === '') {
            return $this->jsonBody;
        }

        return $this->jsonBody[$key] ?? $default;
    }

    public function query(string $key = '', mixed $default = null): mixed
    {
        if ($key === '') {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function post(string $key = '', mixed $default = null): mixed
    {
        if ($key === '') {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    public function server(string $key = '', mixed $default = null): mixed
    {
        if ($key === '') {
            return $this->server;
        }

        $key = strtoupper(str_replace('-', '_', $key));

        return $this->server[$key] ?? $default;
    }

    public function header(string $key = '', mixed $default = null): mixed
    {
        if ($key === '') {
            return $this->headers;
        }

        $normalized = strtolower(str_replace('_', '-', $key));

        return $this->headers[$normalized] ?? $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function ip(): string
    {
        return $this->header('X-Forwarded-For', '')
            ?: $this->server('REMOTE_ADDR', '0.0.0.0');
    }

    public function userAgent(): string
    {
        return $this->header('User-Agent', '');
    }

    public function is(string ...$methods): bool
    {
        return in_array($this->method(), array_map('strtoupper', $methods), true);
    }

    public function isGet(): bool
    {
        return $this->is('GET');
    }

    public function isPost(): bool
    {
        return $this->is('POST');
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With', '') === 'XMLHttpRequest';
    }

    public function expectsJson(): bool
    {
        $accept = $this->header('Accept', '');
        return str_contains($accept, 'application/json') || $this->isAjax();
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent();
    }

    public function merge(array $data): self
    {
        $clone = clone $this;
        $clone->body = array_merge($clone->body, $data);
        return $clone;
    }

    private function parseHeaders(): void
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if ($headers !== false) {
                foreach ($headers as $key => $value) {
                    $this->headers[strtolower($key)] = $value;
                }
                return;
            }
        }

        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerKey = strtolower(str_replace('_', '-', substr($key, 5)));
                $this->headers[$headerKey] = $value;
            }
        }

        foreach (['CONTENT_TYPE', 'CONTENT_LENGTH'] as $key) {
            if (isset($this->server[$key])) {
                $this->headers[strtolower(str_replace('_', '-', $key))] = $this->server[$key];
            }
        }
    }
}
