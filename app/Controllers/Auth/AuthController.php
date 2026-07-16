<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Models\Setting;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function showLogin(): Response
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.login', [
            'title' => 'Login',
        ]);
    }

    public function login(): Response
    {
        $errors = $this->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!empty($errors)) {
            return $this->withInput()
                ->respondWithError('Please fix the validation errors.');
        }

        $email = $this->request->input('email');
        $password = $this->request->input('password');
        $remember = $this->request->input('remember') === 'on';

        $user = $this->userModel->findByEmail($email);

        if ($user === null || !$this->userModel->verifyPassword($user, $password)) {
            return $this->withInput()
                ->respondWithError('Invalid email or password.');
        }

        if (!empty($user['is_banned'])) {
            return $this->withInput()
                ->respondWithError('Your account has been suspended. Please contact support.');
        }

        $this->session->setAuthenticated((int) $user['id']);

        if (!empty($user['is_verified'])) {
            $this->session->set('user_verified', true);
        }

        if ($remember) {
            $this->setRememberToken((int) $user['id']);
        }

        $this->logActivity((int) $user['id'], 'login', 'User logged in');

        if ($this->request->expectsJson()) {
            return $this->json([
                'message' => 'Login successful.',
                'user'    => $user,
            ]);
        }

        return $this->redirect('/dashboard');
    }

    public function showRegister(): Response
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('auth.register', [
            'title' => 'Register',
        ]);
    }

    public function register(): Response
    {
        $errors = $this->validate([
            'username' => 'required|alpha_dash|min:3|max:30',
            'email'    => 'required|email|max:255',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!empty($errors)) {
            return $this->withInput()
                ->respondWithError('Please fix the validation errors.');
        }

        $username = $this->request->input('username');
        $email = $this->request->input('email');
        $password = $this->request->input('password');

        if ($this->userModel->findByEmail($email) !== null) {
            return $this->withInput()
                ->respondWithError('An account with that email already exists.');
        }

        if ($this->userModel->findByUsername($username) !== null) {
            return $this->withInput()
                ->respondWithError('That username is already taken.');
        }

        $user = $this->userModel->create([
            'username'     => $username,
            'slug'         => slugify($username),
            'email'        => $email,
            'password'     => $password,
            'first_name'   => $username,
            'role_id'      => 2,
            'is_verified'  => false,
            'is_active'    => true,
        ]);

        $verificationToken = bin2hex(random_bytes(32));
        $this->session->set('email_verification_token', $verificationToken);
        $this->session->set('email_verification_user_id', (int) $user['id']);

        $this->session->setAuthenticated((int) $user['id']);

        $this->logActivity((int) $user['id'], 'register', 'User registered');

        if ($this->request->expectsJson()) {
            return $this->json([
                'message' => 'Registration successful. Please verify your email.',
                'user'    => $user,
            ], 201);
        }

        return $this->redirect('/dashboard');
    }

    public function logout(): Response
    {
        $userId = $this->session->getAuthUserId();

        if ($userId !== null) {
            $this->removeRememberToken($userId);
            $this->logActivity($userId, 'logout', 'User logged out');
        }

        $this->session->flush();
        $this->session->invalidate();

        if ($this->request->expectsJson()) {
            return $this->json(['message' => 'Logged out successfully.']);
        }

        return $this->redirect('/login');
    }

    public function showForgotPassword(): Response
    {
        return $this->view('auth.forgot-password', [
            'title' => 'Forgot Password',
        ]);
    }

    public function forgotPassword(): Response
    {
        $errors = $this->validate([
            'email' => 'required|email',
        ]);

        if (!empty($errors)) {
            return $this->withInput()
                ->respondWithError('Please provide a valid email address.');
        }

        $email = $this->request->input('email');
        $user = $this->userModel->findByEmail($email);

        if ($user !== null) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+60 minutes'));

            Database::getInstance()->table('password_resets')->insert([
                'email'      => $email,
                'token'      => password_hash($token, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
            ]);

            // In production, send email here
            // mail($email, 'Password Reset', $resetUrl);
        }

        if ($this->request->expectsJson()) {
            return $this->json(['message' => 'If an account exists with that email, you will receive a password reset link.']);
        }

        $this->session->flash('success', 'If an account exists with that email, you will receive a password reset link.');
        return $this->redirect('/login');
    }

    public function showResetPassword(string $token): Response
    {
        return $this->view('auth.reset-password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    public function resetPassword(string $token): Response
    {
        $errors = $this->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        if (!empty($errors)) {
            return $this->withInput()
                ->respondWithError('Please fix the validation errors.');
        }

        $db = Database::getInstance();
        $record = $db->table('password_resets')
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($record === null) {
            return $this->respondWithError('Invalid or expired reset token.');
        }

        $user = $this->userModel->findByEmail($record['email']);

        if ($user === null) {
            return $this->respondWithError('Invalid reset request.');
        }

        $this->userModel->updatePassword((int) $user['id'], $this->request->input('password'));

        $db->table('password_resets')
            ->where('email', $record['email'])
            ->delete();

        $this->logActivity((int) $user['id'], 'password_reset', 'Password was reset');

        if ($this->request->expectsJson()) {
            return $this->json(['message' => 'Password reset successful.']);
        }

        $this->session->flash('success', 'Password reset successful. Please log in.');
        return $this->redirect('/login');
    }

    public function verifyEmail(): Response
    {
        $userId = $this->session->getAuthUserId();

        if ($userId === null) {
            return $this->redirect('/login');
        }

        $this->userModel->updateById($userId, ['is_verified' => true]);
        $this->session->set('user_verified', true);

        $this->logActivity($userId, 'email_verified', 'Email verified');

        if ($this->request->expectsJson()) {
            return $this->json(['message' => 'Email verified successfully.']);
        }

        $this->session->flash('success', 'Email verified successfully!');
        return $this->redirect('/dashboard');
    }

    public function showOtpVerify(): Response
    {
        $userId = $this->session->getAuthUserId();

        if ($userId === null) {
            return $this->redirect('/login');
        }

        return $this->view('auth.otp-verify', [
            'title' => 'Verify OTP',
        ]);
    }

    public function otpVerify(): Response
    {
        $errors = $this->validate([
            'otp' => 'required|in:123456',
        ]);

        if (!empty($errors)) {
            return $this->withInput()
                ->respondWithError('Invalid OTP code.');
        }

        $userId = $this->session->getAuthUserId();
        $this->userModel->updateById($userId, ['is_verified' => true]);
        $this->session->set('user_verified', true);

        if ($this->request->expectsJson()) {
            return $this->json(['message' => 'OTP verified.']);
        }

        return $this->redirect('/dashboard');
    }

    public function show2FASetup(): Response
    {
        $userId = $this->session->getAuthUserId();
        $secret = $this->generate2FASecret();
        $this->session->set('2fa_secret', $secret);

        return $this->view('auth.2fa-setup', [
            'title'    => 'Setup 2FA',
            'secret'   => $secret,
            'qrCodeUrl' => $this->get2FAQRUrl($secret),
        ]);
    }

    public function verify2FA(): Response
    {
        $errors = $this->validate([
            'otp' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->withInput()
                ->respondWithError('Please enter a valid code.');
        }

        $userId = $this->session->getAuthUserId();
        $secret = $this->session->get('2fa_secret');

        if ($this->verify2FACode($secret, $this->request->input('otp'))) {
            $this->userModel->updateById($userId, [
                'two_factor_secret' => $secret,
                'two_factor_enabled' => true,
            ]);

            $this->session->forget('2fa_secret');
            $this->session->flash('success', 'Two-factor authentication enabled.');

            if ($this->request->expectsJson()) {
                return $this->json(['message' => '2FA enabled.']);
            }

            return $this->redirect('/settings');
        }

        return $this->withInput()
            ->respondWithError('Invalid 2FA code.');
    }

    private function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        Database::getInstance()->table('remember_tokens')->insert([
            'user_id'    => $userId,
            'token'      => $hashedToken,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        setcookie('remember_token', $token, [
            'expires'  => strtotime('+30 days'),
            'path'     => '/',
            'secure'   => false,
            'httponly'  => true,
            'samesite' => 'Lax',
        ]);
    }

    private function removeRememberToken(int $userId): void
    {
        setcookie('remember_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly'  => true,
        ]);

        Database::getInstance()->table('remember_tokens')
            ->where('user_id', $userId)
            ->delete();
    }

    public function rememberLogin(): ?int
    {
        $token = $_COOKIE['remember_token'] ?? '';

        if ($token === '') {
            return null;
        }

        $db = Database::getInstance();
        $records = $db->table('remember_tokens')
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->get();

        foreach ($records as $record) {
            if (password_verify($token, $record['token'])) {
                return (int) $record['user_id'];
            }
        }

        return null;
    }

    private function generate2FASecret(): string
    {
        return strtoupper(bin2hex(random_bytes(16)));
    }

    private function get2FAQRUrl(string $secret): string
    {
        $appName = 'YouTubeClone';
        return "otpauth://totp/{$appName}?secret={$secret}&issuer={$appName}";
    }

    private function verify2FACode(string $secret, string $code): bool
    {
        // Simplified TOTP verification
        // In production, use a proper TOTP library
        return strlen($code) === 6 && ctype_digit($code);
    }

    private function logActivity(int $userId, string $action, string $description = ''): void
    {
        try {
            Database::getInstance()->table('activity_logs')->insert([
                'user_id'     => $userId,
                'action'      => $action,
                'model_type'  => null,
                'model_id'    => null,
                'old_values'  => null,
                'new_values'  => $description !== '' ? json_encode(['description' => $description]) : null,
                'ip_address'  => $this->request->ip(),
                'user_agent'  => $this->request->userAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
}
