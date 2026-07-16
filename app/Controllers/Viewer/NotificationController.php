<?php

declare(strict_types=1);

namespace App\Controllers\Viewer;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $notificationModel = new Notification();
        $page = (int) $this->request->query('page', 1);

        $notifications = $notificationModel->getUserNotifications($userId, 30, $page);
        $unreadCount = $notificationModel->getUnreadCount($userId);

        return $this->view('viewer.notifications', [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $notificationId = (int) $this->request->input('notification_id');

        $notificationModel = new Notification();
        $notificationModel->markAsRead($notificationId);

        if ($this->request->expectsJson()) {
            return $this->json(['status' => 'ok']);
        }

        return $this->back();
    }

    public function markAllRead(): Response
    {
        $userId = (int) $this->session->get('user_id');

        $notificationModel = new Notification();
        $notificationModel->markAllAsRead($userId);

        if ($this->request->expectsJson()) {
            return $this->json(['status' => 'ok']);
        }

        $this->session->flash('success', 'All notifications marked as read.');
        return $this->redirect('/viewer/notifications');
    }

    public function delete(): Response
    {
        $userId = (int) $this->session->get('user_id');

        $notificationModel = new Notification();
        $notificationModel->deleteAll($userId);

        if ($this->request->expectsJson()) {
            return $this->json(['status' => 'ok']);
        }

        $this->session->flash('success', 'All notifications deleted.');
        return $this->redirect('/viewer/notifications');
    }

    public function check(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $notificationModel = new Notification();
        $count = $notificationModel->getUnreadCount($userId);
        return $this->json(['count' => $count]);
    }
}
