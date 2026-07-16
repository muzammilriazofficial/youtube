<?php

declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected Request $request;

    protected Response $response;

    protected Router $router;

    protected Session $session;

    protected View $view;

    public function __construct()
    {
        $this->request  = Request::getInstance();
        $this->response = new Response();
        $this->session  = Session::getInstance();
        $this->view     = View::getInstance();
    }

    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }

    protected function view(string $view, array $data = [], int $statusCode = 200): Response
    {
        $data['_controller'] = $this;

        $html = $this->view->render($view, $data);

        return new Response($html, $statusCode, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    protected function json(mixed $data, int $statusCode = 200, array $headers = []): Response
    {
        return Response::json($data, $statusCode, $headers);
    }

    protected function redirect(string $url, int $statusCode = 302, array $headers = []): Response
    {
        return Response::redirect($url, $statusCode, $headers);
    }

    protected function redirectRoute(string $name, array $params = [], int $statusCode = 302): Response
    {
        $url = $this->router->route($name, $params);
        return $this->redirect($url, $statusCode);
    }

    protected function back(): Response
    {
        $referer = $this->request->header('referer', '/');
        return $this->redirect($referer);
    }

    protected function withSuccess(string $message): self
    {
        $this->session->flash('success', $message);
        return $this;
    }

    protected function withError(string $message): self
    {
        $this->session->flash('error', $message);
        return $this;
    }

    protected function withInput(): self
    {
        $this->session->flash('old_input', $this->request->all());
        return $this;
    }

    protected function authorized(): bool
    {
        return $this->session->has('user_id');
    }

    protected function user(): ?array
    {
        $userId = $this->session->get('user_id');

        if ($userId === null) {
            return null;
        }

        $cached = $this->session->get('current_user');

        if ($cached !== null) {
            return $cached;
        }

        $userModel = new \App\Models\User();
        $user = $userModel->find((int) $userId);

        if ($user !== null) {
            $this->session->set('current_user', $user);
        }

        return $user;
    }

    protected function getUser(): ?array
    {
        return $this->user();
    }

    protected function isAuthenticated(): bool
    {
        return $this->session->isAuthenticated();
    }

    protected function guest(): bool
    {
        return !$this->isAuthenticated();
    }

    protected function hasRole(string ...$roles): bool
    {
        $currentUser = $this->user();

        if ($currentUser === null) {
            return false;
        }

        if (!empty($currentUser['is_admin'])) {
            return true;
        }

        $roleModel = new \App\Models\Role();
        return $roleModel->userHasAnyRole((int) $currentUser['id'], $roles);
    }

    protected function can(string ...$permissions): bool
    {
        $currentUser = $this->user();

        if ($currentUser === null) {
            return false;
        }

        if (!empty($currentUser['is_admin'])) {
            return true;
        }

        $permModel = new \App\Models\Permission();
        return $permModel->userHasAnyPermission((int) $currentUser['id'], $permissions);
    }

    protected function cannot(string $permission): bool
    {
        return !$this->can($permission);
    }

    protected function hasAnyRole(array $roles): bool
    {
        $currentUser = $this->user();

        if ($currentUser === null) {
            return false;
        }

        if (!empty($currentUser['is_admin'])) {
            return true;
        }

        $roleModel = new \App\Models\Role();
        return $roleModel->userHasAnyRole((int) $currentUser['id'], $roles);
    }

    protected function hasAnyPermission(array $permissions): bool
    {
        $currentUser = $this->user();

        if ($currentUser === null) {
            return false;
        }

        if (!empty($currentUser['is_admin'])) {
            return true;
        }

        $permModel = new \App\Models\Permission();
        return $permModel->userHasAnyPermission((int) $currentUser['id'], $permissions);
    }

    protected function validateCsrf(): bool
    {
        $token = $this->request->input('_token', '');

        if ($token === '') {
            $token = $this->request->header('X-CSRF-Token', '');
        }

        $sessionToken = $this->session->get('csrf_token');

        if ($sessionToken === null || $sessionToken === '' || $token === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    protected function validate(array $rules): array
    {
        $errors = [];
        $data   = $this->request->all();

        foreach ($rules as $field => $ruleSet) {
            $fieldRules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value      = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $params = [];

                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                switch ($rule) {
                    case 'required':
                        if ($value === null || $value === '') {
                            $errors[$field][] = "{$field} is required.";
                        }
                        break;

                    case 'email':
                        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "{$field} must be a valid email address.";
                        }
                        break;

                    case 'min':
                        if ($value !== null && strlen($value) < (int) ($params[0] ?? 0)) {
                            $errors[$field][] = "{$field} must be at least {$params[0]} characters.";
                        }
                        break;

                    case 'max':
                        if ($value !== null && strlen($value) > (int) ($params[0] ?? 255)) {
                            $errors[$field][] = "{$field} must not exceed {$params[0]} characters.";
                        }
                        break;

                    case 'numeric':
                        if ($value !== null && $value !== '' && !is_numeric($value)) {
                            $errors[$field][] = "{$field} must be numeric.";
                        }
                        break;

                    case 'confirmed':
                        $confirmField = $field . '_confirmation';
                        if (($data[$confirmField] ?? null) !== $value) {
                            $errors[$field][] = "{$field} confirmation does not match.";
                        }
                        break;

                    case 'in':
                        if ($value !== null && !in_array($value, $params, true)) {
                            $errors[$field][] = "{$field} must be one of: " . implode(', ', $params) . ".";
                        }
                        break;

                    case 'url':
                        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $errors[$field][] = "{$field} must be a valid URL.";
                        }
                        break;

                    case 'alpha':
                        if ($value !== null && !preg_match('/^[a-zA-Z]+$/', $value)) {
                            $errors[$field][] = "{$field} must contain only letters.";
                        }
                        break;

                    case 'alpha_dash':
                        if ($value !== null && !preg_match('/^[a-zA-Z0-9_\-]+$/', $value)) {
                            $errors[$field][] = "{$field} must contain only letters, numbers, dashes, and underscores.";
                        }
                        break;

                    case 'file':
                        if ($value !== null && !is_uploaded_file($value['tmp_name'] ?? '')) {
                            $errors[$field][] = "{$field} must be a valid uploaded file.";
                        }
                        break;

                    case 'image':
                        if ($value !== null && is_array($value)) {
                            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                            $finfo   = finfo_open(FILEINFO_MIME_TYPE);
                            $type    = finfo_file($finfo, $value['tmp_name'] ?? '');
                            finfo_close($finfo);
                            if (!in_array($type, $allowed, true)) {
                                $errors[$field][] = "{$field} must be an image (JPEG, PNG, GIF, WebP).";
                            }
                        }
                        break;
                }

                if (isset($errors[$field])) {
                    break;
                }
            }
        }

        return $errors;
    }

    protected function respondWithError(string $message, int $statusCode = 400): Response
    {
        if ($this->isApiRequest()) {
            return $this->json(['error' => $message], $statusCode);
        }

        $this->session->flash('error', $message);
        return $this->back();
    }

    protected function isApiRequest(): bool
    {
        $accept = $this->request->header('Accept', '');
        return str_contains($accept, 'application/json')
            || $this->request->expectsJson();
    }
}
