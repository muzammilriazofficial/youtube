<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\RawExpression;

class Video extends Model
{
    protected string $table = 'videos';

    protected bool $timestamps = true;

    protected bool $softDeletes = true;

    protected array $fillable = [
        'channel_id',
        'title',
        'slug',
        'description',
        'filename',
        'file_path',
        'file_size',
        'duration',
        'width',
        'height',
        'codec',
        'thumbnail_path',
        'hls_path',
        'dash_path',
        'visibility',
        'status',
        'is_short',
        'is_live',
        'view_count',
        'like_count',
        'dislike_count',
        'comments_count',
        'shares_count',
        'category_id',
        'published_at',
        'scheduled_at',
        'processing_status',
        'copyright_status',
        'monetization_status',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'file_size'      => 'integer',
        'duration'       => 'integer',
        'width'          => 'integer',
        'height'         => 'integer',
        'view_count'     => 'integer',
        'like_count'     => 'integer',
        'dislike_count'  => 'integer',
        'comments_count' => 'integer',
        'shares_count'   => 'integer',
    ];

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function getChannel(int $videoId): ?array
    {
        $video = $this->find($videoId);
        if ($video === null) {
            return null;
        }

        return (new Channel())->find((int) $video['channel_id']);
    }

    public function getCategory(int $videoId): ?array
    {
        $video = $this->find($videoId);
        if ($video === null) {
            return null;
        }

        return (new Category())->find((int) $video['category_id']);
    }

    public function getComments(int $videoId, int $limit = 20): array
    {
        return $this->db->table('comments')
            ->where('video_id', $videoId)
            ->whereNull('parent_id')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getRelatedVideos(int $videoId, int $limit = 12): array
    {
        $video = $this->find($videoId);
        if ($video === null) {
            return [];
        }

        $sameCategory = $this->db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
            ->where('videos.category_id', $video['category_id'])
            ->where('videos.id', '!=', $videoId)
            ->where('videos.visibility', 'public')
            ->whereNull('videos.deleted_at')
            ->orderBy('videos.view_count', 'DESC')
            ->limit($limit)
            ->get();

        if (count($sameCategory) >= $limit) {
            return $sameCategory;
        }

        $existingIds = array_column($sameCategory, 'id');
        $existingIds[] = $videoId;

        $placeholders = implode(',', array_fill(0, count($existingIds), '?'));

        $moreVideos = $this->db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
            ->where('videos.id', '!=', $videoId)
            ->where('videos.visibility', 'public')
            ->whereNull('videos.deleted_at')
            ->orderBy('videos.view_count', 'DESC')
            ->limit($limit - count($sameCategory))
            ->get();

        $filtered = [];
        foreach ($moreVideos as $vid) {
            if (!in_array($vid['id'], $existingIds)) {
                $filtered[] = $vid;
            }
        }

        return array_merge($sameCategory, $filtered);
    }

    public function incrementViewCount(int $videoId): bool
    {
        return $this->db->table('videos')
            ->where('id', $videoId)
            ->update([
                'view_count' => new RawExpression('view_count + 1'),
            ]) > 0;
    }

    public function incrementLikeCount(int $videoId): bool
    {
        return $this->db->table('videos')
            ->where('id', $videoId)
            ->update([
                'like_count' => new RawExpression('like_count + 1'),
            ]) > 0;
    }

    public function decrementLikeCount(int $videoId): bool
    {
        return $this->db->table('videos')
            ->where('id', $videoId)
            ->update([
                'like_count' => new RawExpression('GREATEST(like_count - 1, 0)'),
            ]) > 0;
    }

    public function incrementDislikeCount(int $videoId): bool
    {
        return $this->db->table('videos')
            ->where('id', $videoId)
            ->update([
                'dislike_count' => new RawExpression('dislike_count + 1'),
            ]) > 0;
    }

    public function decrementDislikeCount(int $videoId): bool
    {
        return $this->db->table('videos')
            ->where('id', $videoId)
            ->update([
                'dislike_count' => new RawExpression('GREATEST(dislike_count - 1, 0)'),
            ]) > 0;
    }

    public function getPublicVideos(int $limit = 20, int $page = 1): array
    {
        return $this->where('visibility', 'public')
            ->orderBy('published_at', 'DESC')
            ->paginate($limit, $page);
    }

    public function getTrendingVideos(int $limit = 20): array
    {
        $oneWeekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

        return $this->db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->where('videos.visibility', 'public')
            ->where('videos.published_at', '>', $oneWeekAgo)
            ->whereNull('videos.deleted_at')
            ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
            ->orderBy('videos.view_count', 'DESC')
            ->limit($limit)
            ->get();
    }
}
