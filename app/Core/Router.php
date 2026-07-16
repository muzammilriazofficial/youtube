<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    private array $namedRoutes = [];

    private array $middleware = [];

    private array $groupStack = [];

    private string $currentGroupPrefix = '';

    private array $currentGroupMiddleware = [];

    private ?Request $request = null;

    public function __construct(?Request $request = null)
    {
        $this->request = $request;
    }

    public function get(string $path, array|callable $action, string $name = ''): self
    {
        return $this->addRoute('GET', $path, $action, $name);
    }

    public function post(string $path, array|callable $action, string $name = ''): self
    {
        return $this->addRoute('POST', $path, $action, $name);
    }

    public function put(string $path, array|callable $action, string $name = ''): self
    {
        return $this->addRoute('PUT', $path, $action, $name);
    }

    public function patch(string $path, array|callable $action, string $name = ''): self
    {
        return $this->addRoute('PATCH', $path, $action, $name);
    }

    public function delete(string $path, array|callable $action, string $name = ''): self
    {
        return $this->addRoute('DELETE', $path, $action, $name);
    }

    public function resource(string $name, string $controllerClass): self
    {
        $plural = $name;

        $this->get("/{$plural}", [$controllerClass, 'index'], "{$name}.index");
        $this->get("/{$plural}/create", [$controllerClass, 'create'], "{$name}.create");
        $this->post("/{$plural}", [$controllerClass, 'store'], "{$name}.store");
        $this->get("/{$plural}/{{{id}}}", [$controllerClass, 'show'], "{$name}.show");
        $this->get("/{$plural}/{{{id}}}/edit", [$controllerClass, 'edit'], "{$name}.edit");
        $this->put("/{$plural}/{{{id}}}", [$controllerClass, 'update'], "{$name}.update");
        $this->delete("/{$plural}/{{{id}}}", [$controllerClass, 'destroy'], "{$name}.destroy");

        return $this;
    }

    public function group(string $prefix, callable $callback, array $middleware = []): self
    {
        $previousPrefix         = $this->currentGroupPrefix;
        $previousMiddleware     = $this->currentGroupMiddleware;

        $this->currentGroupPrefix     = $previousPrefix . '/' . trim($prefix, '/');
        $this->currentGroupMiddleware = array_merge($this->currentGroupMiddleware, $middleware);

        $callback($this);

        $this->currentGroupPrefix     = $previousPrefix;
        $this->currentGroupMiddleware = $previousMiddleware;

        return $this;
    }

    public function middleware(array $middleware): self
    {
        $this->currentGroupMiddleware = array_merge($this->currentGroupMiddleware, $middleware);
        return $this;
    }

    private function addRoute(string $method, string $path, array|callable $action, string $name): self
    {
        $fullPath = $this->currentGroupPrefix . '/' . trim($path, '/');
        $fullPath = $fullPath === '' ? '/' : $fullPath;

        if ($fullPath !== '/') {
            $fullPath = rtrim($fullPath, '/');
        }

        $route = [
            'method'     => $method,
            'path'       => $fullPath,
            'action'     => $action,
            'middleware'  => $this->currentGroupMiddleware,
            'regex'      => $this->buildRegex($fullPath),
            'parameters' => $this->extractParameters($fullPath),
        ];

        $this->routes[] = $route;

        if ($name !== '') {
            $this->namedRoutes[$name] = $route;
        }

        return $this;
    }

    private function buildRegex(string $path): string
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $regex . '$#';
    }

    private function extractParameters(string $path): array
    {
        preg_match_all('/\{([a-zA-Z_]+)\}/', $path, $matches);
        return $matches[1];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri    = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $allMiddleware = $route['middleware'];

                if (!empty($allMiddleware)) {
                    $pipeline = Middleware::getInstance();
                    $result   = $pipeline->run($request, $allMiddleware, function (Request $req) use ($route, $params) {
                        return $this->callAction($route['action'], $params);
                    });

                    return $result instanceof Response ? $result : new Response((string) $result);
                }

                $result = $this->callAction($route['action'], $params);
                return $result instanceof Response ? $result : new Response((string) $result);
            }
        }

        $accept = $request->header('Accept', '');
        if (str_contains($accept, 'application/json')) {
            return new Response(json_encode(['error' => 'Not Found', 'message' => 'The requested resource was not found.']), 404, ['Content-Type' => 'application/json']);
        }

        return new Response($this->renderNotFound(), 404);
    }

    private function callAction(array|callable $action, array $params): mixed
    {
        if ($action instanceof \Closure) {
            return $action(...array_values($params));
        }

        [$controllerClass, $methodName] = $action;

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller class {$controllerClass} not found.");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            throw new \RuntimeException("Method {$methodName} not found on {$controllerClass}.");
        }

        return $controller->$methodName(...array_values($params));
    }

    public function route(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Route [{$name}] not defined.");
        }

        $path = $this->namedRoutes[$name]['path'];

        foreach ($this->namedRoutes[$name]['parameters'] as $param) {
            if (isset($params[$param])) {
                $path = str_replace('{' . $param . '}', (string) $params[$param], $path);
                unset($params[$param]);
            }
        }

        if (!empty($params)) {
            $path .= '?' . http_build_query($params);
        }

        return $path;
    }

    public function hasRoute(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }

    private function renderNotFound(): string
    {
        return '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1><p>The page you requested was not found.</p></body></html>';
    }
}
