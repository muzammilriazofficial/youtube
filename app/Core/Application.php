<?php

declare(strict_types=1);

namespace App\Core;

class Application
{
    private static ?self $instance = null;

    private string $rootPath;

    private Autoloader $autoloader;

    private Router $router;

    private Request $request;

    private Response $response;

    private Session $session;

    private View $view;

    private Middleware $middleware;

    private array $config = [];

    private bool $booted = false;

    public function __construct(string $rootPath)
    {
        $this->rootPath = rtrim($rootPath, DIRECTORY_SEPARATOR);

        self::$instance = $this;

        $this->defineConstants();
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->loadEnvironment();
        $this->setupAutoloader();
        $this->loadConfig();
        $this->loadHelpers();
        $this->setupErrorHandling();
        $this->setupSession();
        $this->setupRequest();
        $this->setupMiddleware();
        $this->setupRouter();
        $this->bootMiddlewareAliases();
        $this->bootCsrfProtection();

        $this->booted = true;
    }

    public function run(): void
    {
        $this->boot();

        $session = $this->session;
        $userId = $session->get('user_id');
        $currentUser = null;
        if ($userId !== null) {
            $currentUser = $session->get('current_user');
            if ($currentUser === null) {
                $userModel = new \App\Models\User();
                $currentUser = $userModel->find((int) $userId);
            }
            if ($currentUser !== null && (empty($currentUser['avatar']) || empty($currentUser['channel_slug']))) {
                $channelModel = new \App\Models\Channel();
                $channel = $channelModel->findByUserId((int) $userId);
                if ($channel) {
                    if (empty($currentUser['avatar']) && !empty($channel['avatar'])) {
                        $currentUser['avatar'] = $channel['avatar'];
                    }
                    if (empty($currentUser['channel_slug'])) {
                        $currentUser['channel_slug'] = $channel['custom_url'] ?? $channel['slug'] ?? '';
                    }
                }
                $session->set('current_user', $currentUser);
            }
        }

        $unreadNotificationCount = 0;
        if ($currentUser !== null) {
            try {
                $unreadNotificationCount = Database::getInstance()->table('notifications')
                    ->where('user_id', (int) $currentUser['id'])
                    ->whereNull('read_at')
                    ->count();
            } catch (\Throwable $e) {
                // notifications table may not exist
            }
        }

        $view = View::getInstance();
        $requestUri = $this->request->path() ?? '';
        $view->shareAll([
            'currentUser' => $currentUser,
            'unreadNotificationCount' => $unreadNotificationCount,
            'studioMode' => str_starts_with($requestUri, '/creator'),
        ]);

        try {
            $response = $this->router->dispatch($this->request);
            $response->send();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getConfig(string $key = '', mixed $default = null): mixed
    {
        if ($key === '') {
            return $this->config;
        }

        $keys   = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getView(): View
    {
        return $this->view;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getMiddleware(): Middleware
    {
        return $this->middleware;
    }

    public function isDebug(): bool
    {
        return $this->config['app']['debug'] ?? false;
    }

    private function defineConstants(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', $this->rootPath);
        }

        if (!defined('APP_PATH')) {
            define('APP_PATH', $this->rootPath . DIRECTORY_SEPARATOR . 'app');
        }

        if (!defined('CONFIG_PATH')) {
            define('CONFIG_PATH', $this->rootPath . DIRECTORY_SEPARATOR . 'config');
        }

        if (!defined('PUBLIC_PATH')) {
            define('PUBLIC_PATH', $this->rootPath . DIRECTORY_SEPARATOR . 'public');
        }

        if (!defined('STORAGE_PATH')) {
            define('STORAGE_PATH', $this->rootPath . DIRECTORY_SEPARATOR . 'storage');
        }

        if (!defined('VIEW_PATH')) {
            define('VIEW_PATH', $this->rootPath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views');
        }
    }

    private function loadEnvironment(): void
    {
        $envFile = $this->rootPath . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);

                $key   = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");

                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }
    }

    private function setupAutoloader(): void
    {
        $this->autoloader = Autoloader::boot($this->rootPath);
    }

    private function loadConfig(): void
    {
        $configFile = $this->rootPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }

        if (isset($this->config['app']['timezone'])) {
            date_default_timezone_set($this->config['app']['timezone']);
        }
    }

    private function setupErrorHandling(): void
    {
        $debug = $this->isDebug();

        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use ($debug) {
            $message = "{$errstr} in {$errfile}:{$errline}";

            if ($debug) {
                error_log("[ERROR] {$message}");
            }

            if (error_reporting() & $errno) {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        });

        set_exception_handler(function (\Throwable $e) use ($debug) {
            $this->handleException($e);
        });

        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                $this->handleException(new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                ));
            }
        });
    }

    private function setupSession(): void
    {
        $this->session = Session::getInstance();
        $this->session->start();
    }

    private function setupRequest(): void
    {
        $this->request  = Request::capture();
        $this->response = new Response();
    }

    private function setupMiddleware(): void
    {
        $this->middleware = Middleware::getInstance();
    }

    private function setupRouter(): void
    {
        $this->router = new Router($this->request);

        $this->registerGlobalMiddleware();
        $this->loadRoutes();
    }

    private function registerGlobalMiddleware(): void
    {
        $middlewareConfig = $this->getConfig('middleware', []);

        if (!empty($middlewareConfig['global'])) {
            foreach ($middlewareConfig['global'] as $name => $handler) {
                $this->middleware->register($name, $handler);
            }
        }
    }

    private function bootMiddlewareAliases(): void
    {
        $aliases = [
            'auth'           => \App\Middleware\AuthMiddleware::class,
            'guest'          => \App\Middleware\GuestMiddleware::class,
            'role'           => \App\Middleware\RoleMiddleware::class,
            'permission'     => \App\Middleware\PermissionMiddleware::class,
            'verified'       => \App\Middleware\VerifiedMiddleware::class,
            'ban'            => \App\Middleware\BanMiddleware::class,
            'throttle'       => \App\Middleware\RateLimitMiddleware::class,
            'csrf'           => \App\Middleware\CsrfMiddleware::class,
            'api.auth'       => \App\Middleware\ApiAuthMiddleware::class,
            'api.cors'       => \App\Middleware\CorsMiddleware::class,
            'api.ratelimit'  => \App\Middleware\ApiRateLimitMiddleware::class,
        ];

        foreach ($aliases as $alias => $className) {
            $this->middleware->alias($alias, $className);
        }

        $customAliases = $this->getConfig('middleware.aliases', []);

        foreach ($customAliases as $alias => $className) {
            $this->middleware->alias($alias, $className);
        }
    }

    private function bootCsrfProtection(): void
    {
        $session = $this->session;

        if (!$session->has('csrf_token')) {
            $session->set('csrf_token', bin2hex(random_bytes(32)));
        }
    }

    private function loadRoutes(): void
    {
        $routesFile = $this->rootPath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';

        if (file_exists($routesFile)) {
            require $routesFile;
        }

        $apiRoutesFile = $this->rootPath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';

        if (file_exists($apiRoutesFile)) {
            require $apiRoutesFile;
        }
    }

    private function loadHelpers(): void
    {
        $helpersFile = $this->rootPath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR . 'helpers.php';

        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }

    private function handleException(\Throwable $e): void
    {
        $debug = $this->isDebug();

        http_response_code(500);

        if ($this->request && $this->request->expectsJson()) {
            header('Content-Type: application/json');
            $payload = [
                'error'   => 'Internal Server Error',
                'message' => $debug ? $e->getMessage() : 'An unexpected error occurred.',
            ];
            if ($debug) {
                $payload['trace'] = $e->getTraceAsString();
                $payload['file']  = $e->getFile();
                $payload['line']  = $e->getLine();
            }
            echo json_encode($payload);
            return;
        }

        if (headers_sent()) {
            echo '<pre>';
            echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            echo '</pre>';
            return;
        }

        $template = $debug
            ? $this->renderDebugError($e)
            : $this->renderProductionError();

        echo $template;
    }

    private function renderDebugError(\Throwable $e): string
    {
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file    = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $trace   = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        $line    = $e->getLine();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Error</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; background: #1a1a2e; color: #e0e0e0; }
        .error-container { max-width: 900px; margin: 0 auto; }
        h1 { color: #ff6b6b; font-size: 28px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .meta { color: #888; font-size: 14px; margin-bottom: 20px; }
        .message { background: #16213e; border-left: 4px solid #ff6b6b; padding: 20px; margin: 20px 0; font-size: 16px; line-height: 1.6; }
        pre { background: #0f3460; padding: 20px; border-radius: 8px; overflow-x: auto; font-size: 13px; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; }
        code { font-family: 'Fira Code', 'Cascadia Code', Consolas, monospace; }
        h3 { color: #aaa; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Application Error</h1>
        <div class="meta">{$file}:{$line}</div>
        <div class="message">{$message}</div>
        <h3>Stack Trace</h3>
        <pre><code>{$trace}</code></pre>
    </div>
</body>
</html>
HTML;
    }

    private function renderProductionError(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #0f0f0f; color: #ccc; }
        .error { text-align: center; }
        h1 { font-size: 72px; margin: 0; color: #ff4444; }
        p { font-size: 18px; margin-top: 15px; color: #888; }
        a { color: #4488ff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="error">
        <h1>500</h1>
        <p>Something went wrong. Please try again later.</p>
        <p><a href="/">Go Home</a></p>
    </div>
</body>
</html>
HTML;
    }
}
