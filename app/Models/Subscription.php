<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Subscription extends Model
{
    protected string $table = 'subscriptions';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'subscriber_id',
        'channel_id',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [];

    public function isSubscribed(int $userId, int $channelId): bool
    {
        return $this->where('subscriber_id', $userId)
            ->where('channel_id', $channelId)
            ->exists();
    }

    public function getSubscriberChannels(int $userId, int $limit = 100): array
    {
        return $this->db->table('subscriptions')
            ->join('channels', 'subscriptions.channel_id', '=', 'channels.id')
            ->where('subscriptions.subscriber_id', $userId)
            ->orderBy('subscriptions.created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getChannelSubscribers(int $channelId, int $limit = 100): array
    {
        return $this->db->table('subscriptions')
            ->join('users', 'subscriptions.subscriber_id', '=', 'users.id')
            ->where('subscriptions.channel_id', $channelId)
            ->orderBy('subscriptions.created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getSubscriberCount(int $channelId): int
    {
        return $this->where('channel_id', $channelId)->count();
    }

    public function getSubscriptionFeed(int $userId, int $limit = 20, int $page = 1): array
    {
        $channels = $this->getSubscriberChannels($userId, 500);
        $channelIds = array_column($channels, 'channel_id');

        if (empty($channelIds)) {
            return ['data' => [], 'total' => 0];
        }

        return $this->db->table('videos')
            ->whereIn('channel_id', $channelIds)
            ->where('visibility', 'public')
            ->whereNull('deleted_at')
            ->orderBy('published_at', 'DESC')
            ->paginate($limit, $page);
    }
}
