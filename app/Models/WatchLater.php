<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class WatchLater extends Model
{
    protected string $table = 'watch_later';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'user_id',
        'video_id',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [];

    public function getUserList(int $userId, int $limit = 50, int $page = 1): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, $page);
    }

    public function isWatchLater(int $userId, int $videoId): bool
    {
        return $this->where('user_id', $userId)
            ->where('video_id', $videoId)
            ->exists();
    }

    public function toggle(int $userId, int $videoId): string
    {
        if ($this->isWatchLater($userId, $videoId)) {
            $this->db->table('watch_later')
                ->where('user_id', $userId)
                ->where('video_id', $videoId)
                ->delete();

            return 'removed';
        }

        $this->db->table('watch_later')->insert([
            'user_id'    => $userId,
            'video_id'   => $videoId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return 'added';
    }

    public function addToWatchLater(int $userId, int $videoId): bool
    {
        if ($this->isWatchLater($userId, $videoId)) {
            return true;
        }

        return (bool) $this->db->table('watch_later')->insert([
            'user_id'    => $userId,
            'video_id'   => $videoId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function removeFromWatchLater(int $userId, int $videoId): bool
    {
        return $this->db->table('watch_later')
            ->where('user_id', $userId)
            ->where('video_id', $videoId)
            ->delete() > 0;
    }

    public function clearWatchLater(int $userId): bool
    {
        return $this->db->table('watch_later')
            ->where('user_id', $userId)
            ->delete() > 0;
    }
}
