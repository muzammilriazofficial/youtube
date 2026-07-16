<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Response;
use App\Core\Database;
use App\Models\User;
use App\Models\Channel;
use App\Models\Subscription;
use App\Models\WatchHistory;
use App\Models\Notification;
use App\Services\SecurityService;

class UserApiController extends ApiController
{
    public function profile(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        if ($this->request->method() === 'PUT') {
            return $this->updateProfile();
        }

        $channelModel = new Channel();
        $channel = $channelModel->findByUserId((int) $this->apiUser['id']);

        $result = [
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
        ];

        return $this->success($result);
    }

    private function updateProfile(): Response
    {
        $errors = $this->validate([
            'display_name' => 'max:50',
            'description'  => 'max:500',
        ]);

        if (!empty($errors)) {
            return $this->error('Validation failed.', 422, $errors);
        }

        $data = $this->request->only(['display_name', 'description', 'avatar', 'banner']);

        if (isset($data['display_name'])) {
            $data['display_name'] = $this->sanitize($data['display_name']);
        }
        if (isset($data['description'])) {
            $data['description'] = $this->sanitize($data['description']);
        }

        $userModel = new User();
        $userModel->updateById((int) $this->apiUser['id'], $data);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'profile_updated'
        );

        $updatedUser = $userModel->find((int) $this->apiUser['id']);

        return $this->success([
            'id'           => $updatedUser['id'],
            'username'     => $updatedUser['username'],
            'email'        => $updatedUser['email'],
            'display_name' => $updatedUser['display_name'],
            'avatar'       => $updatedUser['avatar'] ?? null,
            'description'  => $updatedUser['description'] ?? null,
        ], 'Profile updated successfully.');
    }

    public function subscriptions(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $subModel  = new Subscription();
        $channels  = $subModel->getSubscriberChannels((int) $this->apiUser['id'], 100);

        return $this->success($channels, 'Your subscriptions.');
    }

    public function history(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $page  = $this->getPage();
        $limit = $this->getPerPage();

        $historyModel = new WatchHistory();
        $history      = $historyModel->getUserHistory((int) $this->apiUser['id'], $limit, $page);

        return $this->paginatedResponse($history, 'Watch history retrieved.');
    }

    public function notifications(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $page  = $this->getPage();
        $limit = $this->getPerPage();

        $notiModel     = new Notification();
        $notifications = $notiModel->getUserNotifications((int) $this->apiUser['id'], $limit, $page);

        $unreadCount = $notiModel->getUnreadCount((int) $this->apiUser['id']);

        $response = $this->paginatedResponse($notifications, 'Notifications retrieved.');

        $decoded = json_decode($response->getContent(), true);
        $decoded['meta']['unread_count'] = $unreadCount;
        $response->setContent(json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response;
    }

    public function markNotificationsRead(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $notiModel = new Notification();
        $notiModel->markAllAsRead((int) $this->apiUser['id']);

        return $this->success(null, 'All notifications marked as read.');
    }
}
