<?php

declare(strict_types=1);

namespace App\Core;

class Middleware
{
    private array $registered = [];

    private static ?self $instance = null;

    private array $middlewareAliases = [
        'auth'       => \App\Middleware\AuthMiddleware::class,
        'guest'      => \App\Middleware\GuestMiddleware::class,
        'role'       => \App\Middleware\RoleMiddleware::class,
        'permission' => \App\Middleware\PermissionMiddleware::class,
        'verified'   => \App\Middleware\VerifiedMiddleware::class,
        'ban'        => \App\Middleware\BanMiddleware::class,
        'throttle'   => \App\Middleware\RateLimitMiddleware::class,
        'csrf'       => \App\Middleware\CsrfMiddleware::class,
    ];

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

    public function register(string $name, callable|string $middleware): void
    {
        $this->registered[$name] = $middleware;
    }

    public function registerMany(array $middlewares): void
    {
        foreach ($middlewares as $name => $handler) {
            $this->register($name, $handler);
        }
    }

    public function alias(string $alias, string $className): void
    {
        $this->middlewareAliases[$alias] = $className;
    }

    public function getRegistered(): array
    {
        return $this->registered;
    }

    public function run(Request $request, array $middlewares, callable $core): Response
    {
        $pipeline = $this->buildPipeline($middlewares, $core);
        return $pipeline($request);
    }

    private function buildPipeline(array $middlewares, callable $core): callable
    {
        $coreFunction = function (Request $request) use ($core) {
            $result = $core($request);
            return $result instanceof Response ? $result : new Response((string) $result);
        };

        $pipeline = $coreFunction;

        foreach (array_reverse($middlewares) as $middleware) {
            $pipeline = $this->wrapMiddleware($middleware, $pipeline);
        }

        return $pipeline;
    }

    private function wrapMiddleware(mixed $middleware, callable $next): callable
    {
        return function (Request $request) use ($middleware, $next) {
            if (is_callable($middleware)) {
                $result = $middleware($request, $next);

                if ($result instanceof Response) {
                    return $result;
                }

                if (is_string($result)) {
                    return new Response($result);
                }

                return $next($request);
            }

            if (is_string($middleware)) {
                return $this->resolveAndRun($middleware, $request, $next);
            }

            if (is_array($middleware) && count($middleware) === 2) {
                return $this->resolveArrayMiddleware($middleware, $request, $next);
            }

            throw new \RuntimeException('Invalid middleware type. Must be callable, string, or array.');
        };
    }

    private function resolveAndRun(string $name, Request $request, callable $next): Response
    {
        $params = [];
        $middlewareName = $name;

        if (str_contains($name, ':')) {
            $parts = explode(':', $name, 2);
            $middlewareName = $parts[0];
            $params = array_map('trim', explode(',', $parts[1]));
        }

        $resolved = $this->resolveMiddleware($middlewareName);

        if (is_callable($resolved)) {
            $result = $resolved($request, $next, ...$params);

            if ($result instanceof Response) {
                return $result;
            }

            if (is_string($result)) {
                return new Response($result);
            }

            return $next($request);
        }

        if (is_string($resolved) && class_exists($resolved)) {
            return $this->runClassMiddlewareWithParams($resolved, $request, $next, $params);
        }

        if (class_exists($middlewareName)) {
            return $this->runClassMiddlewareWithParams($middlewareName, $request, $next, $params);
        }

        throw new \RuntimeException("Middleware [{$name}] could not be resolved.");
    }

    private function runClassMiddlewareWithParams(string $className, Request $request, callable $next, array $params = []): Response
    {
        $instance = new $className();

        if (!method_exists($instance, 'handle')) {
            throw new \RuntimeException("Middleware [{$className}] must have a handle() method.");
        }

        $result = $instance->handle($request, $next, ...$params);

        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result)) {
            return new Response($result);
        }

        return $next($request);
    }

    private function resolveArrayMiddleware(array $middleware, Request $request, callable $next): Response
    {
        [$class, $params] = $middleware;

        if (!is_string($class) || !class_exists($class)) {
            throw new \RuntimeException("Middleware class [{$class}] not found.");
        }

        $instance = new $class();

        if (!method_exists($instance, 'handle')) {
            throw new \RuntimeException("Middleware [{$class}] must have a handle() method.");
        }

        $result = $instance->handle($request, $next, ...$params);

        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result)) {
            return new Response($result);
        }

        return $next($request);
    }

    private function runClassMiddleware(string $className, Request $request, callable $next): Response
    {
        $instance = new $className();

        if (!method_exists($instance, 'handle')) {
            throw new \RuntimeException("Middleware [{$className}] must have a handle() method.");
        }

        $result = $instance->handle($request, $next);

        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result)) {
            return new Response($result);
        }

        return $next($request);
    }

    private function resolveMiddleware(string $name): callable|string|null
    {
        if (isset($this->registered[$name])) {
            return $this->registered[$name];
        }

        if (isset($this->middlewareAliases[$name])) {
            return $this->middlewareAliases[$name];
        }

        return null;
    }
}
