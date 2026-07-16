<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Response;
use App\Core\Database;
use App\Models\User;
use App\Services\SecurityService;

class AuthApiController extends ApiController
{
    public function login(): Response
    {
        $errors = $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422, $errors);
        }

        $email    = $this->request->input('email');
        $password = $this->request->input('password');

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user === null || !$userModel->verifyPassword($user, $password)) {
            $security = SecurityService::getInstance();
            $security->logLogin(0, $email, 'failed', $this->request->ip(), $this->request->userAgent());

            return $this->error('Invalid email or password.', 401);
        }

        if (!empty($user['is_banned'])) {
            return $this->error('Your account has been suspended.', 403);
        }

        $security  = SecurityService::getInstance();
        $tokenData = $security->generateApiToken((int) $user['id'], 'API Login Token');

        $security->logLogin((int) $user['id'], $email, 'success', $this->request->ip(), $this->request->userAgent());
        $security->logActivity((int) $user['id'], 'api_login');

        return $this->success([
            'token'      => $tokenData['token'],
            'expires_at' => $tokenData['expires_at'],
            'user'       => [
                'id'           => $user['id'],
                'username'     => $user['username'],
                'email'        => $user['email'],
                'display_name' => $user['display_name'],
                'avatar'       => $user['avatar'] ?? null,
                'is_verified'  => $user['is_verified'] ?? false,
                'is_admin'     => $user['is_admin'] ?? false,
            ],
        ], 'Login successful.');
    }

    public function register(): Response
    {
        $errors = $this->validate([
            'username'              => 'required|alpha_dash|min:3|max:30',
            'email'                 => 'required|email|max:255',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422, $errors);
        }

        $username = $this->sanitize($this->request->input('username'));
        $email    = $this->sanitize($this->request->input('email'));
        $password = $this->request->input('password');

        $userModel = new User();

        if ($userModel->findByEmail($email) !== null) {
            return $this->error('An account with this email already exists.', 409);
        }

        if ($userModel->findByUsername($username) !== null) {
            return $this->error('This username is already taken.', 409);
        }

        $user = $userModel->create([
            'username'     => $username,
            'email'        => $email,
            'password'     => $password,
            'display_name' => $username,
            'is_verified'  => false,
            'is_admin'     => false,
        ]);

        $security  = SecurityService::getInstance();
        $tokenData = $security->generateApiToken((int) $user['id'], 'API Registration Token');

        $security->logActivity((int) $user['id'], 'registered');
        $security->logLogin((int) $user['id'], $email, 'success', $this->request->ip(), $this->request->userAgent());

        return $this->created([
            'token'      => $tokenData['token'],
            'expires_at' => $tokenData['expires_at'],
            'user'       => [
                'id'           => $user['id'],
                'username'     => $user['username'],
                'email'        => $user['email'],
                'display_name' => $user['display_name'],
            ],
        ], 'Registration successful.');
    }

    public function logout(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $token = $this->request->bearerToken();
        if ($token !== null) {
            SecurityService::getInstance()->revokeApiToken($token);
        }

        SecurityService::getInstance()->logActivity((int) $this->apiUser['id'], 'api_logout');

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $channelModel = new \App\Models\Channel();
        $channel = $channelModel->findByUserId((int) $this->apiUser['id']);

        return $this->success([
            'id'           => $this->apiUser['id'],
            'username'     => $this->apiUser['username'],
            'email'        => $this->apiUser['email'],
            'display_name' => $this->apiUser['display_name'],
            'avatar'       => $this->apiUser['avatar'] ?? null,
            'banner'       => $this->apiUser['banner'] ?? null,
            'description'  => $this->apiUser['description'] ?? null,
            'is_verified'  => $this->apiUser['is_verified'] ?? false,
            'is_admin'     => $this->apiUser['is_admin'] ?? false,
            'created_at'   => $this->apiUser['created_at'] ?? null,
            'channel'      => $channel !== null ? [
                'id'                => $channel['id'],
                'name'              => $channel['name'],
                'slug'              => $channel['slug'],
                'subscriber_count'  => $channel['subscriber_count'] ?? 0,
                'video_count'       => $channel['video_count'] ?? 0,
            ] : null,
        ]);
    }

    public function forgotPassword(): Response
    {
        $errors = $this->validate([
            'email' => 'required|email',
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422, $errors);
        }

        $email     = $this->request->input('email');
        $userModel = new User();
        $user      = $userModel->findByEmail($email);

        if ($user !== null) {
            $security      = SecurityService::getInstance();
            $resetToken    = $security->generateResetToken();
            $hashedToken   = hash('sha256', $resetToken);
            $expiresAt     = date('Y-m-d H:i:s', strtotime('+1 hour'));

            Database::getInstance()->table('password_resets')->insert([
                'user_id'    => (int) $user['id'],
                'token'      => $hashedToken,
                'expires_at' => $expiresAt,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $security->logActivity((int) $user['id'], 'password_reset_requested');
        }

        return $this->success(null, 'If the email exists, a password reset link has been sent.');
    }

    public function resetPassword(): Response
    {
        $errors = $this->validate([
            'token'    => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422, $errors);
        }

        $token       = $this->request->input('token');
        $password    = $this->request->input('password');
        $hashedToken = hash('sha256', $token);

        $db = Database::getInstance();
        $record = $db->table('password_resets')
            ->where('token', $hashedToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if ($record === null) {
            return $this->error('Invalid or expired reset token.', 422);
        }

        $userModel = new User();
        $userModel->updatePassword((int) $record['user_id'], $password);

        $db->table('password_resets')
            ->where('token', $hashedToken)
            ->delete();

        SecurityService::getInstance()->logActivity((int) $record['user_id'], 'password_reset_completed');
        SecurityService::getInstance()->revokeAllUserTokens((int) $record['user_id']);

        return $this->success(null, 'Password has been reset successfully. Please login with your new password.');
    }
}
