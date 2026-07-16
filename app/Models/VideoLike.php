<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class VideoLike extends Model
{
    protected string $table = 'video_likes';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'user_id',
        'video_id',
        'type',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [];

    public function getUserReaction(int $userId, int $videoId): ?array
    {
        return $this->where('user_id', $userId)
            ->where('video_id', $videoId)
            ->first();
    }

    public function toggleLike(int $userId, int $videoId): string
    {
        $existing = $this->getUserReaction($userId, $videoId);
        $videoModel = new Video();

        if ($existing === null) {
            $this->create([
                'user_id'  => $userId,
                'video_id' => $videoId,
                'type'     => 'like',
            ]);

            $videoModel->incrementLikeCount($videoId);
            return 'liked';
        }

        if ($existing['type'] === 'like') {
            $this->deleteById((int) $existing['id']);
            $videoModel->decrementLikeCount($videoId);
            return 'removed';
        }

        $this->updateById((int) $existing['id'], ['type' => 'like']);
        $videoModel->incrementLikeCount($videoId);
        $videoModel->decrementDislikeCount($videoId);
        return 'liked';
    }

    public function toggleDislike(int $userId, int $videoId): string
    {
        $existing = $this->getUserReaction($userId, $videoId);
        $videoModel = new Video();

        if ($existing === null) {
            $this->create([
                'user_id'  => $userId,
                'video_id' => $videoId,
                'type'     => 'dislike',
            ]);

            $videoModel->incrementDislikeCount($videoId);
            return 'disliked';
        }

        if ($existing['type'] === 'dislike') {
            $this->deleteById((int) $existing['id']);
            $videoModel->decrementDislikeCount($videoId);
            return 'removed';
        }

        $this->updateById((int) $existing['id'], ['type' => 'dislike']);
        $videoModel->incrementDislikeCount($videoId);
        $videoModel->decrementLikeCount($videoId);
        return 'disliked';
    }

    public function getLikeCount(int $videoId): int
    {
        return $this->where('video_id', $videoId)
            ->where('type', 'like')
            ->count();
    }

    public function getDislikeCount(int $videoId): int
    {
        return $this->where('video_id', $videoId)
            ->where('type', 'dislike')
            ->count();
    }
}
