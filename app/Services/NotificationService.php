<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Notification;
use App\Models\Subscription;

/**
 * Notification service for creating and managing user notifications.
 */
class NotificationService
{
    private Database $db;
    private Notification $notificationModel;

    public function __construct()
    {
        $this->db               = Database::getInstance();
        $this->notificationModel = new Notification();
    }

    /**
     * Create and store a notification for a single user.
     */
    public function send(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?int $fromUserId = null
    ): array {
        return $this->notificationModel->createNotification(
            $userId,
            $fromUserId ?? 0,
            $type,
            $title,
            $message,
            $data ?? []
        );
    }

    /**
     * Send a notification to multiple users.
     */
    public function sendBulk(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?int $fromUserId = null
    ): int {
        if (empty($userIds)) {
            return 0;
        }

        $now    = date('Y-m-d H:i:s');
        $rows   = [];

        foreach ($userIds as $userId) {
            $rows[] = [
                'user_id'      => $userId,
                'from_user_id' => $fromUserId ?? 0,
                'type'         => $type,
                'title'        => $title,
                'message'      => $message,
                'data'         => json_encode($data ?? []),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        return $this->db->table('notifications')->insertBatch($rows);
    }

    /**
     * Notify all subscribers of a channel.
     */
    public function sendToSubscribers(
        int $channelId,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?int $fromUserId = null
    ): int {
        $subscriptionModel = new Subscription();
        $subscribers       = $subscriptionModel->getChannelSubscribers($channelId, 10000);

        $userIds = array_column($subscribers, 'subscriber_id');

        if (empty($userIds)) {
            return 0;
        }

        return $this->sendBulk($userIds, $type, $title, $message, $data, $fromUserId);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $affected = $this->db->table('notifications')
            ->where('id', $notificationId)
            ->where('user_id', $userId)
            ->update([
                'read_at'    => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $affected > 0;
    }

    /**
     * Mark all notifications for a user as read.
     */
    public function markAllAsRead(int $userId): bool
    {
        $affected = $this->db->table('notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update([
                'read_at'    => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        return $affected >= 0;
    }

    /**
     * Get the count of unread notifications.
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->notificationModel->getUnreadCount($userId);
    }

    /**
     * Delete a notification.
     */
    public function delete(int $notificationId, int $userId): bool
    {
        $affected = $this->db->table('notifications')
            ->where('id', $notificationId)
            ->where('user_id', $userId)
            ->delete();

        return $affected > 0;
    }

    /**
     * Get notifications for a user with pagination.
     */
    public function getNotifications(int $userId, int $perPage = 20, int $page = 1): array
    {
        return $this->notificationModel->getUserNotifications($userId, $perPage, $page);
    }

    /**
     * Delete all notifications for a user.
     */
    public function clearAll(int $userId): bool
    {
        return $this->notificationModel->deleteAll($userId);
    }
}
