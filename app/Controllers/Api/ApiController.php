<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Database;
use App\Models\User;
use App\Services\SecurityService;

class ApiController extends Controller
{
    protected ?array $apiUser = null;

    protected ?array $apiTokenRecord = null;

    protected SecurityService $securityService;

    public function __construct()
    {
        parent::__construct();
        $this->securityService = SecurityService::getInstance();
    }

    protected function authenticateToken(): ?array
    {
        $token = $this->request->bearerToken();

        if ($token === null) {
            return null;
        }

        $user = $this->securityService->validateApiToken($token);

        if ($user !== null) {
            $this->apiUser = $user;
            $this->apiTokenRecord = $this->securityService->getTokenRecord($token);
        }

        return $user;
    }

    protected function getApiUser(): ?array
    {
        if ($this->apiUser !== null) {
            return $this->apiUser;
        }

        return $this->authenticateToken();
    }

    protected function requireAuth(): Response|bool
    {
        if ($this->authenticateToken() === null) {
            return $this->jsonResponse(
                'error',
                'Unauthenticated. Please provide a valid API token.',
                null,
                401
            );
        }
        return true;
    }

    protected function jsonResponse(
        string $status,
        string $message,
        mixed $data = null,
        int $statusCode = 200,
        ?array $meta = null,
        ?array $errors = null
    ): Response {
        $payload = [
            'status'  => $status,
            'message' => $message,
            'data'    => $data,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        $headers = array_merge(
            $this->securityService->getSecurityHeaders(),
            ['Content-Type' => 'application/json']
        );

        return Response::json($payload, $statusCode, $headers);
    }

    protected function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): Response
    {
        return $this->jsonResponse('success', $message, $data, $statusCode);
    }

    protected function created(mixed $data = null, string $message = 'Created successfully.'): Response
    {
        return $this->jsonResponse('success', $message, $data, 201);
    }

    protected function deleted(string $message = 'Deleted successfully.'): Response
    {
        return $this->jsonResponse('success', $message, null, 200);
    }

    protected function noContent(string $message = 'No content.'): Response
    {
        return $this->jsonResponse('success', $message, null, 204);
    }

    protected function error(string $message, int $statusCode = 400, array $errors = []): Response
    {
        return $this->jsonResponse('error', $message, null, $statusCode, null, !empty($errors) ? $errors : null);
    }

    protected function paginatedResponse(array $paginatedData, string $message = 'Success'): Response
    {
        $meta = [
            'current_page'   => $paginatedData['current_page'] ?? 1,
            'per_page'       => $paginatedData['per_page'] ?? 20,
            'total'          => $paginatedData['total'] ?? 0,
            'last_page'      => $paginatedData['last_page'] ?? 1,
            'has_more_pages' => $paginatedData['has_more_pages'] ?? false,
            'has_prev_page'  => $paginatedData['has_prev_page'] ?? false,
        ];

        return $this->jsonResponse('success', $message, $paginatedData['data'] ?? [], 200, $meta);
    }

    protected function authorizeOwnership(array $resource, string $ownerKey = 'user_id'): Response|bool
    {
        if ($this->apiUser === null) {
            return $this->error('Unauthenticated.', 401);
        }

        if (!empty($this->apiUser['is_admin'])) {
            return true;
        }

        if ((int) ($resource[$ownerKey] ?? 0) !== (int) $this->apiUser['id']) {
            return $this->error('Unauthorized. You do not own this resource.', 403);
        }

        return true;
    }

    protected function authorizeChannelOwnership(array $channel): Response|bool
    {
        if ($this->apiUser === null) {
            return $this->error('Unauthenticated.', 401);
        }

        if (!empty($this->apiUser['is_admin'])) {
            return true;
        }

        if ((int) ($channel['user_id'] ?? 0) !== (int) $this->apiUser['id']) {
            return $this->error('Unauthorized. You do not own this channel.', 403);
        }

        return true;
    }

    protected function sanitize(array|string $input): array|string
    {
        return $this->securityService->sanitizeInput($input);
    }

    protected function getPage(): int
    {
        return max(1, (int) $this->request->query('page', 1));
    }

    protected function getPerPage(int $max = 50): int
    {
        return min(max(1, (int) $this->request->query('per_page', 20)), $max);
    }

    protected function getSort(string $default = 'latest'): string
    {
        return $this->request->query('sort', $default);
    }

    protected function applyCorsHeaders(Response $response): Response
    {
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        $response->header('Access-Control-Expose-Headers', 'X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');
        return $response;
    }
}
