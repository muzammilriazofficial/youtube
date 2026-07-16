<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Response;
use App\Models\Notification;
use App\Services\SecurityService;

class NotificationApiController extends ApiController
{
    public function index(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $page  = $this->getPage();
        $limit = $this->getPerPage();

        $notiModel     = new Notification();
        $notifications = $notiModel->getUserNotifications((int) $this->apiUser['id'], $limit, $page);
        $unreadCount   = $notiModel->getUnreadCount((int) $this->apiUser['id']);

        $response = $this->paginatedResponse($notifications, 'Notifications retrieved.');

        $decoded = json_decode($response->getContent(), true);
        $decoded['meta']['unread_count'] = $unreadCount;
        $response->setContent(json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response;
    }

    public function read(string $id): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $notiModel    = new Notification();
        $notification = $notiModel->find((int) $id);

        if ($notification === null) {
            return $this->error('Notification not found.', 404);
        }

        if ((int) $notification['user_id'] !== (int) $this->apiUser['id']) {
            return $this->error('Unauthorized.', 403);
        }

        $notiModel->markAsRead((int) $id);

        return $this->success(null, 'Notification marked as read.');
    }

    public function readAll(): Response
    {
        $auth = $this->requireAuth();
        if ($auth instanceof Response) {
            return $auth;
        }

        $notiModel = new Notification();
        $notiModel->markAllAsRead((int) $this->apiUser['id']);

        SecurityService::getInstance()->logActivity(
            (int) $this->apiUser['id'],
            'notifications_read_all'
        );

        return $this->success(null, 'All notifications marked as read.');
    }
}
