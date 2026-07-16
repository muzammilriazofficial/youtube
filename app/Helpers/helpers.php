<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Session;

if (!function_exists('e')) {
    function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = '', array $params = []): string
    {
        $app  = Application::getInstance();
        $base = $app ? $app->getConfig('app.url', '') : '';

        $url = rtrim($base, '/') . '/' . ltrim($path, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}

if (!function_exists('asset')) {
    function asset(string $path = ''): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $session = Session::getInstance();
        return $session->token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_token" value="' . e($token) . '">';
    }
}

if (!function_exists('csrf_token_check')) {
    function csrf_token_check(string $token): bool
    {
        $session = Session::getInstance();
        return $session->validateToken($token);
    }
}

if (!function_exists('time_ago')) {
    function time_ago(string $datetime): string
    {
        $time = strtotime($datetime);

        if ($time === false) {
            return '';
        }

        $diff = time() - $time;

        if ($diff < 0) {
            return 'just now';
        }

        $intervals = [
            31536000 => 'year',
            2592000  => 'month',
            604800   => 'week',
            86400    => 'day',
            3600     => 'hour',
            60       => 'minute',
            1        => 'second',
        ];

        foreach ($intervals as $seconds => $label) {
            $count = floor($diff / $seconds);

            if ($count >= 1) {
                return $count . ' ' . $label . ($count > 1 ? 's' : '') . ' ago';
            }
        }

        return 'just now';
    }
}

if (!function_exists('format_number')) {
    function format_number(int|float $number, int $decimals = 0): string
    {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . 'B';
        }

        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        }

        if ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }

        return number_format($number, $decimals);
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text, string $separator = '-'): string
    {
        $text = preg_replace('~[^\pL\d]+~u', $separator, $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, $separator);
        $text = preg_replace('~-' . preg_quote($separator, '~') . '-~', $separator, $text);
        $text = strtolower($text);

        return $text !== '' ? $text : 'n-a';
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = null): mixed
    {
        $session = Session::getInstance();
        $old     = $session->getFlash('old_input', []);

        return $old[$key] ?? $default;
    }
}

if (!function_exists('now')) {
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $statusCode = 302): \App\Core\Response
    {
        return \App\Core\Response::redirect($url, $statusCode);
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$values): never
    {
        foreach ($values as $value) {
            echo '<pre>';
            var_dump($value);
            echo '</pre>';
        }
        exit(1);
    }
}

if (!function_exists('dump')) {
    function dump(mixed ...$values): void
    {
        foreach ($values as $value) {
            echo '<pre>';
            var_dump($value);
            echo '</pre>';
        }
    }
}

if (!function_exists('d')) {
    function d(mixed ...$values): void
    {
        foreach ($values as $value) {
            echo '<pre>';
            var_dump($value);
            echo '</pre>';
        }
    }
}

if (!function_exists('json_response')) {
    function json_response(mixed $data, int $statusCode = 200): \App\Core\Response
    {
        return \App\Core\Response::json($data, $statusCode);
    }
}

if (!function_exists('human_file_size')) {
    function human_file_size(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
