<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    protected string $content;

    protected int $statusCode;

    protected array $headers;

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content    = $content;
        $this->statusCode = $statusCode;
        $this->headers    = array_merge([
            'Content-Type' => 'text/html; charset=UTF-8',
        ], $headers);
    }

    public static function json(mixed $data, int $statusCode = 200, array $headers = []): self
    {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return new self($content, $statusCode, array_merge([
            'Content-Type' => 'application/json',
        ], $headers));
    }

    public static function html(string $content, int $statusCode = 200, array $headers = []): self
    {
        return new self($content, $statusCode, array_merge([
            'Content-Type' => 'text/html; charset=UTF-8',
        ], $headers));
    }

    public static function redirect(string $url, int $statusCode = 302, array $headers = []): self
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $url = url($url);
        }

        return new self('', $statusCode, array_merge([
            'Location' => $url,
        ], $headers));
    }

    public static function back(): self
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return self::redirect($referer);
    }

    public static function noContent(int $statusCode = 204): self
    {
        return new self('', $statusCode);
    }

    public static function created(mixed $data = null): self
    {
        if ($data === null) {
            return new self('', 201);
        }
        return self::json($data, 201);
    }

    public static function ok(mixed $data = null): self
    {
        if ($data === null) {
            return new self('', 200);
        }
        return self::json($data, 200);
    }

    public static function error(string $message, int $statusCode = 400, array $errors = []): self
    {
        $payload = ['error' => $message];
        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }
        return self::json($payload, $statusCode);
    }

    public static function notFound(string $message = 'Not Found'): self
    {
        return self::json(['error' => $message], 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return self::json(['error' => $message], 401);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return self::json(['error' => $message], 403);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function cookie(string $name, string $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = true): self
    {
        setcookie($name, $value, [
            'expires'  => $expires,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly'  => $httponly,
            'samesite' => 'Lax',
        ]);
        return $this;
    }

    public function withCookie(string $name, string $value, int $expires = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = true): self
    {
        $clone = clone $this;
        $clone->cookie($name, $value, $expires, $path, $domain, $secure, $httponly);
        return $clone;
    }

    public function cache(int $minutes, bool $private = false): self
    {
        $visibility = $private ? 'private' : 'public';
        $this->headers['Cache-Control'] = "{$visibility}, max-age=" . ($minutes * 60);
        return $this;
    }

    public function noCache(): self
    {
        $this->headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0';
        $this->headers['Pragma']        = 'no-cache';
        return $this;
    }

    public function download(string $filename, string $content, string $contentType = 'application/octet-stream'): self
    {
        $this->headers['Content-Type']        = $contentType;
        $this->headers['Content-Disposition'] = 'attachment; filename="' . $filename . '"';
        $this->headers['Content-Transfer-Encoding'] = 'binary';

        $this->content    = $content;
        $this->statusCode = 200;

        return $this;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function isRedirect(): bool
    {
        return in_array($this->statusCode, [301, 302, 303, 307, 308], true);
    }

    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }
}
