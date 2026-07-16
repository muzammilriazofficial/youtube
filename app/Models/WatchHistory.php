<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class WatchHistory extends Model
{
    protected string $table = 'watch_history';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'user_id',
        'video_id',
        'watch_duration',
        'progress',
        'completed',
        'last_watched_at',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'watch_duration' => 'integer',
        'progress'       => 'integer',
        'completed'      => 'boolean',
    ];

    public function getUserHistory(int $userId, int $limit = 50, int $page = 1): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('last_watched_at', 'DESC')
            ->paginate($limit, $page);
    }

    public function recordWatch(int $userId, int $videoId, int $duration, int $progress, bool $completed = false): bool
    {
        $existing = $this->db->table('watch_history')
            ->where('user_id', $userId)
            ->where('video_id', $videoId)
            ->first();

        if ($existing) {
            return $this->updateById((int) $existing['id'], [
                'watch_duration' => $duration,
                'progress'       => $progress,
                'completed'      => $completed,
                'last_watched_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return (bool) $this->db->table('watch_history')->insert([
            'user_id'         => $userId,
            'video_id'        => $videoId,
            'watch_duration'  => $duration,
            'progress'        => $progress,
            'completed'       => $completed,
            'last_watched_at' => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    public function removeFromHistory(int $userId, int $videoId): bool
    {
        return $this->db->table('watch_history')
            ->where('user_id', $userId)
            ->where('video_id', $videoId)
            ->delete() > 0;
    }

    public function clearHistory(int $userId): bool
    {
        return $this->db->table('watch_history')
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function getContinueWatching(int $userId, int $limit = 20): array
    {
        return $this->where('user_id', $userId)
            ->where('progress', '<', 100)
            ->orderBy('updated_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getProgress(int $userId, int $videoId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('video_id', $videoId)
            ->first();
    }
}
