<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Comment extends Model
{
    protected string $table = 'comments';

    protected bool $timestamps = true;

    protected bool $softDeletes = true;

    protected array $fillable = [
        'user_id',
        'video_id',
        'parent_id',
        'body',
        'like_count',
        'replies_count',
        'is_pinned',
        'is_hearted',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'like_count'  => 'integer',
        'replies_count' => 'integer',
        'is_pinned'   => 'boolean',
        'is_hearted'  => 'boolean',
    ];

    public function getUser(int $commentId): ?array
    {
        $comment = $this->find($commentId);
        if ($comment === null) {
            return null;
        }

        return (new User())->find((int) $comment['user_id']);
    }

    public function getVideo(int $commentId): ?array
    {
        $comment = $this->find($commentId);
        if ($comment === null) {
            return null;
        }

        return (new Video())->find((int) $comment['video_id']);
    }

    public function getReplies(int $commentId, int $limit = 20, int $page = 1): array
    {
        return $this->where('parent_id', $commentId)
            ->orderBy('created_at', 'ASC')
            ->paginate($limit, $page);
    }

    public function getVideoComments(int $videoId, int $limit = 20, int $page = 1): array
    {
        $result = $this->db->table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->leftJoin('channels', 'channels.user_id', '=', 'users.id')
            ->select('comments.*', 'users.username as username', 'COALESCE(users.avatar, channels.avatar) as user_avatar')
            ->where('comments.video_id', $videoId)
            ->whereNull('comments.parent_id')
            ->orderBy('comments.created_at', 'DESC')
            ->paginate($limit, $page);

        return $result;
    }

    public function getNestedComments(int $videoId, int $limit = 20): array
    {
        $comments = $this->getVideoComments($videoId, $limit);

        foreach ($comments['data'] as &$comment) {
            $replies = $this->db->table('comments')
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->leftJoin('channels', 'channels.user_id', '=', 'users.id')
                ->select('comments.*', 'users.username as username', 'COALESCE(users.avatar, channels.avatar) as user_avatar')
                ->where('comments.parent_id', $comment['id'])
                ->orderBy('comments.created_at', 'ASC')
                ->limit(3)
                ->get();

            $comment['replies'] = $replies;

            $replyCount = $this->db->table('comments')
                ->where('parent_id', $comment['id'])
                ->count();
            $comment['total_replies'] = $replyCount;
        }

        return $comments;
    }

    public function incrementLikeCount(int $commentId): bool
    {
        return $this->db->table('comments')
            ->where('id', $commentId)
            ->update([
                'like_count' => new \App\Core\RawExpression('like_count + 1'),
            ]) > 0;
    }

    public function decrementLikeCount(int $commentId): bool
    {
        return $this->db->table('comments')
            ->where('id', $commentId)
            ->update([
                'like_count' => new \App\Core\RawExpression('GREATEST(like_count - 1, 0)'),
            ]) > 0;
    }

    public function incrementReplyCount(int $commentId): bool
    {
        return $this->db->table('comments')
            ->where('id', $commentId)
            ->update([
                'replies_count' => new \App\Core\RawExpression('replies_count + 1'),
            ]) > 0;
    }

    public function getPinned(int $videoId): ?array
    {
        return $this->where('video_id', $videoId)
            ->where('is_pinned', true)
            ->first();
    }
}
