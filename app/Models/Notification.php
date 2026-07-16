<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected string $table = 'notifications';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'data' => 'array',
    ];

    public function getUserNotifications(int $userId, int $limit = 20, int $page = 1): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, $page);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(int $notificationId): bool
    {
        return $this->db->table('notifications')
            ->where('id', $notificationId)
            ->update([
                'read_at' => date('Y-m-d H:i:s'),
            ]) > 0;
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->db->table('notifications')
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update([
                'read_at' => date('Y-m-d H:i:s'),
            ]) > 0;
    }

    public function deleteAll(int $userId): bool
    {
        return $this->db->table('notifications')
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function createNotification(int $userId, int $fromUserId, string $type, string $title, string $message, array $data = []): array
    {
        return $this->create([
            'user_id'      => $userId,
            'from_user_id' => $fromUserId,
            'type'         => $type,
            'title'        => $title,
            'message'      => $message,
            'data'         => json_encode($data),
        ]);
    }
}
