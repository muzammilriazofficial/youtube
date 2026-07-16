<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

class SocialAuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function googleRedirect(): Response
    {
        $clientId = env('GOOGLE_CLIENT_ID', '');
        $redirectUri = env('GOOGLE_REDIRECT_URI', url('/auth/google/callback'));
        $scope = 'email profile';

        $url = 'https://accounts.google.com/o/oauth2/v2/auth'
            . '?client_id=' . urlencode($clientId)
            . '&redirect_uri=' . urlencode($redirectUri)
            . '&response_type=code'
            . '&scope=' . urlencode($scope)
            . '&access_type=offline'
            . '&prompt=consent';

        return $this->redirect($url);
    }

    public function googleCallback(): Response
    {
        $code = $this->request->input('code');

        if ($code === null) {
            return $this->redirect('/login');
        }

        $tokenData = $this->exchangeGoogleCode($code);

        if ($tokenData === null) {
            $this->session->flash('error', 'Google authentication failed.');
            return $this->redirect('/login');
        }

        $userInfo = $this->getGoogleUserInfo($tokenData['access_token']);

        if ($userInfo === null) {
            $this->session->flash('error', 'Failed to retrieve Google user info.');
            return $this->redirect('/login');
        }

        return $this->handleSocialLogin('google', $userInfo);
    }

    public function facebookRedirect(): Response
    {
        $appId = env('FACEBOOK_APP_ID', '');
        $redirectUri = env('FACEBOOK_REDIRECT_URI', url('/auth/facebook/callback'));
        $scope = 'email,public_profile';

        $url = 'https://www.facebook.com/v18.0/dialog/oauth'
            . '?client_id=' . urlencode($appId)
            . '&redirect_uri=' . urlencode($redirectUri)
            . '&scope=' . urlencode($scope)
            . '&response_type=code';

        return $this->redirect($url);
    }

    public function facebookCallback(): Response
    {
        $code = $this->request->input('code');

        if ($code === null) {
            return $this->redirect('/login');
        }

        $tokenData = $this->exchangeFacebookCode($code);

        if ($tokenData === null) {
            $this->session->flash('error', 'Facebook authentication failed.');
            return $this->redirect('/login');
        }

        $userInfo = $this->getFacebookUserInfo($tokenData['access_token']);

        if ($userInfo === null) {
            $this->session->flash('error', 'Failed to retrieve Facebook user info.');
            return $this->redirect('/login');
        }

        return $this->handleSocialLogin('facebook', $userInfo);
    }

    private function handleSocialLogin(string $provider, array $socialUser): Response
    {
        $email = $socialUser['email'] ?? '';
        $name = $socialUser['name'] ?? '';
        $avatar = $socialUser['avatar'] ?? '';
        $providerId = $socialUser['provider_id'] ?? '';

        $db = Database::getInstance();
        $existing = $db->table('users')
            ->where('email', $email)
            ->first();

        if ($existing !== null) {
            $db->table('users')
                ->where('id', $existing['id'])
                ->update([
                    'last_login_at' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $this->request->ip(),
                ]);

            $this->session->setAuthenticated((int) $existing['id']);
            $this->logActivity((int) $existing['id'], 'social_login', "Logged in via {$provider}");

            return $this->redirect('/dashboard');
        }

        $user = $this->userModel->create([
            'username'     => $this->generateUsername($name),
            'email'        => $email,
            'password'     => password_bin2hex(random_bytes(32)),
            'display_name' => $name,
            'avatar'       => $avatar,
            'is_verified'  => true,
            'is_admin'     => false,
        ]);

        $db->table('social_accounts')->insert([
            'user_id'       => (int) $user['id'],
            'provider'      => $provider,
            'provider_id'   => $providerId,
            'access_token'  => '',
            'refresh_token' => '',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->session->setAuthenticated((int) $user['id']);
        $this->logActivity((int) $user['id'], 'register_social', "Registered via {$provider}");

        $this->session->flash('success', 'Welcome! Your account has been created.');

        return $this->redirect('/dashboard');
    }

    private function exchangeGoogleCode(string $code): ?array
    {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'code'          => $code,
                'client_id'     => env('GOOGLE_CLIENT_ID', ''),
                'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
                'redirect_uri'  => env('GOOGLE_REDIRECT_URI', url('/auth/google/callback')),
                'grant_type'    => 'authorization_code',
            ]),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response ?? '', true);

        return is_array($data) && isset($data['access_token']) ? $data : null;
    }

    private function getGoogleUserInfo(string $accessToken): ?array
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response ?? '', true);

        if (!is_array($data) || !isset($data['email'])) {
            return null;
        }

        return [
            'email'       => $data['email'],
            'name'        => $data['name'] ?? '',
            'avatar'      => $data['picture'] ?? '',
            'provider_id' => $data['id'] ?? '',
        ];
    }

    private function exchangeFacebookCode(string $code): ?array
    {
        $redirectUri = env('FACEBOOK_REDIRECT_URI', url('/auth/facebook/callback'));

        $ch = curl_init("https://graph.facebook.com/v18.0/oauth/access_token"
            . "?client_id=" . urlencode(env('FACEBOOK_APP_ID', ''))
            . "&client_secret=" . urlencode(env('FACEBOOK_APP_SECRET', ''))
            . "&redirect_uri=" . urlencode($redirectUri)
            . "&code=" . urlencode($code)
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response ?? '', true);

        return is_array($data) && isset($data['access_token']) ? $data : null;
    }

    private function getFacebookUserInfo(string $accessToken): ?array
    {
        $ch = curl_init("https://graph.facebook.com/v18.0/me?fields=id,name,email,picture.type(large)&access_token={$accessToken}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response ?? '', true);

        if (!is_array($data) || !isset($data['email'])) {
            return null;
        }

        return [
            'email'       => $data['email'],
            'name'        => $data['name'] ?? '',
            'avatar'      => $data['picture']['data']['url'] ?? '',
            'provider_id' => $data['id'] ?? '',
        ];
    }

    private function generateUsername(string $name): string
    {
        $base = slugify($name);
        $username = $base;
        $counter = 1;

        while ($this->userModel->findByUsername($username) !== null) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    private function logActivity(int $userId, string $action, string $description = ''): void
    {
        try {
            Database::getInstance()->table('activity_logs')->insert([
                'user_id'     => $userId,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $this->request->ip(),
                'user_agent'  => $this->request->userAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
}
