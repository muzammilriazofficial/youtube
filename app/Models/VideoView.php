<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class VideoView extends Model
{
    protected string $table = 'video_views';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'video_id',
        'user_id',
        'ip_address',
        'user_agent',
        'watch_duration',
        'completed',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'watch_duration' => 'integer',
        'completed'      => 'boolean',
    ];

    public function getViewCount(int $videoId): int
    {
        return $this->where('video_id', $videoId)->count();
    }

    public function getUniqueViewCount(int $videoId): int
    {
        $results = $this->db->table('video_views')
            ->where('video_id', $videoId)
            ->select('COUNT(DISTINCT ip_address) as count')
            ->first();

        return (int) ($results['count'] ?? 0);
    }

    public function getUserViewCount(int $videoId, int $userId): int
    {
        return $this->where('video_id', $videoId)
            ->where('user_id', $userId)
            ->count();
    }

    public function recordView(int $videoId, ?int $userId, string $ipAddress, string $userAgent): bool
    {
        $recentView = $this->db->table('video_views')
            ->where('video_id', $videoId)
            ->where('ip_address', $ipAddress)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($recentView !== null) {
            $diff = strtotime('now') - strtotime($recentView['created_at']);
            if ($diff < 300) {
                return true;
            }
        }

        $result = $this->db->table('video_views')->insert([
            'video_id'      => $videoId,
            'user_id'       => $userId,
            'ip_address'    => $ipAddress,
            'user_agent'    => $userAgent,
            'watch_duration' => 0,
            'completed'     => false,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        if ($result) {
            (new Video())->incrementViewCount($videoId);
        }

        return (bool) $result;
    }

    public function updateWatchDuration(int $videoId, string $ipAddress, int $duration, bool $completed = false): bool
    {
        return $this->db->table('video_views')
            ->where('video_id', $videoId)
            ->where('ip_address', $ipAddress)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->update([
                'watch_duration' => $duration,
                'completed'      => $completed,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]) > 0;
    }

    public function getViewsOverTime(int $videoId, int $days = 30): array
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        return $this->db->table('video_views')
            ->where('video_id', $videoId)
            ->where('created_at', '>=', $startDate)
            ->select('DATE(created_at) as date', 'COUNT(*) as views')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get();
    }
}
