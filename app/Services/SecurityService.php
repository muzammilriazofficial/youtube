<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Models\ActivityLog;

class SecurityService
{
    private static ?self $instance = null;

    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $session = Session::getInstance();
        $session->set('csrf_token', $token);
        return $token;
    }

    public function validateCsrfToken(string $token): bool
    {
        $session = Session::getInstance();
        $stored = $session->get('csrf_token');

        if ($stored === null || $stored === '' || $token === '') {
            return false;
        }

        return hash_equals($stored, $token);
    }

    public function sanitizeInput(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }

        if (is_string($input)) {
            $input = trim($input);
            $input = strip_tags($input);
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            return $input;
        }

        return $input;
    }

    public function sanitizeRaw(string $input): string
    {
        return trim(strip_tags($input));
    }

    public function generateApiToken(int $userId, string $name = 'API Token'): array
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        $tokenId = $this->db->table('api_tokens')->insert([
            'user_id'    => $userId,
            'name'       => $name,
            'token'      => $hashedToken,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'id'         => $tokenId,
            'token'      => $token,
            'expires_at' => $expiresAt,
        ];
    }

    public function validateApiToken(string $token): ?array
    {
        $hashedToken = hash('sha256', $token);

        $record = $this->db->table('api_tokens')
            ->where('token', $hashedToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if ($record === null) {
            return null;
        }

        $userModel = new \App\Models\User();
        $user = $userModel->find((int) $record['user_id']);

        return $user !== null ? $user : null;
    }

    public function getTokenRecord(string $token): ?array
    {
        $hashedToken = hash('sha256', $token);

        return $this->db->table('api_tokens')
            ->where('token', $hashedToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();
    }

    public function revokeApiToken(string $token): bool
    {
        $hashedToken = hash('sha256', $token);

        return $this->db->table('api_tokens')
            ->where('token', $hashedToken)
            ->delete() > 0;
    }

    public function revokeAllUserTokens(int $userId): bool
    {
        return $this->db->table('api_tokens')
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function generateVerifyToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function getSecurityHeaders(): array
    {
        return [
            'X-Content-Type-Options'  => 'nosniff',
            'X-Frame-Options'         => 'DENY',
            'X-XSS-Protection'        => '1; mode=block',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'Permissions-Policy'      => 'camera=(), microphone=(), geolocation=()',
            'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:;",
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Cache-Control'           => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'                  => 'no-cache',
        ];
    }

    public function logActivity(
        int $userId,
        string $action,
        ?string $modelType = null,
        ?int $modelId = null,
        mixed $oldValues = null,
        mixed $newValues = null
    ): void {
        $request = Request::getInstance();

        $properties = array_filter([
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ], fn($v) => $v !== null);

        $this->db->table('activity_logs')->insert([
            'user_id'     => $userId,
            'action'      => $action,
            'description' => $this->buildActivityDescription($action, $modelType, $modelId),
            'properties'  => !empty($properties) ? json_encode($properties) : null,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function logLogin(
        int $userId,
        string $email,
        string $status,
        string $ip,
        string $userAgent
    ): void {
        $this->db->table('activity_logs')->insert([
            'user_id'     => $userId,
            'action'      => "login_{$status}",
            'description' => "Login {$status} for {$email}",
            'properties'  => json_encode([
                'email'      => $email,
                'ip'         => $ip,
                'user_agent' => $userAgent,
            ]),
            'ip_address'  => $ip,
            'user_agent'  => $userAgent,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function logAudit(int $userId, string $action, ?string $details = null): void
    {
        $request = Request::getInstance();

        $this->db->table('activity_logs')->insert([
            'user_id'     => $userId,
            'action'      => "audit:{$action}",
            'description' => $details ?? $action,
            'properties'  => $details !== null ? json_encode(['details' => $details]) : null,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function cleanupExpiredTokens(): int
    {
        return $this->db->table('api_tokens')
            ->where('expires_at', '<', date('Y-m-d H:i:s'))
            ->delete();
    }

    private function buildActivityDescription(string $action, ?string $modelType, ?int $modelId): string
    {
        $desc = ucfirst(str_replace('_', ' ', $action));

        if ($modelType !== null) {
            $desc .= " on {$modelType}";
            if ($modelId !== null) {
                $desc .= " #{$modelId}";
            }
        }

        return $desc;
    }
}
