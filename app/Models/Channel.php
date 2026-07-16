<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Channel extends Model
{
    protected string $table = 'channels';

    protected bool $timestamps = true;

    protected bool $softDeletes = true;

    protected array $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'avatar',
        'banner',
        'custom_url',
        'country',
        'language',
        'website',
        'keywords',
        'subscriber_count',
        'video_count',
        'total_view_count',
        'is_verified',
        'is_partner',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'subscriber_count' => 'integer',
        'video_count'      => 'integer',
        'total_view_count' => 'integer',
        'is_verified'      => 'boolean',
        'is_partner'       => 'boolean',
    ];

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function findByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    public function getVideos(int $channelId, int $limit = 12): array
    {
        return $this->db->table('videos')
            ->where('channel_id', $channelId)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function incrementSubscribers(int $channelId): bool
    {
        return $this->db->table('channels')
            ->where('id', $channelId)
            ->update([
                'subscriber_count' => new \App\Core\RawExpression('subscriber_count + 1'),
            ]) > 0;
    }

    public function decrementSubscribers(int $channelId): bool
    {
        return $this->db->table('channels')
            ->where('id', $channelId)
            ->update([
                'subscriber_count' => new \App\Core\RawExpression('GREATEST(subscriber_count - 1, 0)'),
            ]) > 0;
    }

    public function incrementVideoCount(int $channelId): bool
    {
        return $this->db->table('channels')
            ->where('id', $channelId)
            ->update([
                'video_count' => new \App\Core\RawExpression('video_count + 1'),
            ]) > 0;
    }

    public function recalculateStats(int $channelId): void
    {
        $videoCount = $this->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->count();

        $totalViews = $this->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->sum('view_count');

        $this->db->table('channels')
            ->where('id', $channelId)
            ->update([
                'video_count'      => (int) $videoCount,
                'total_view_count' => (int) $totalViews,
            ]);
    }
}
