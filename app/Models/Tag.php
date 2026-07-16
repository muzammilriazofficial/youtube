<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Tag extends Model
{
    protected string $table = 'tags';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'name',
        'slug',
        'usage_count',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'usage_count' => 'integer',
    ];

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function findByName(string $name): ?array
    {
        return $this->where('name', $name)->first();
    }

    public function getOrCreate(string $name): array
    {
        $tag = $this->findByName($name);

        if ($tag !== null) {
            return $tag;
        }

        return $this->create([
            'name'    => $name,
            'slug'    => slugify($name),
        ]);
    }

    public function getVideos(int $tagId, int $limit = 20): array
    {
        return $this->db->table('video_tags')
            ->join('videos', 'video_tags.video_id', '=', 'videos.id')
            ->where('video_tags.tag_id', $tagId)
            ->whereNull('videos.deleted_at')
            ->orderBy('videos.created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function incrementUsage(int $tagId): bool
    {
        return $this->db->table('tags')
            ->where('id', $tagId)
            ->update([
                'usage_count' => new \App\Core\RawExpression('usage_count + 1'),
            ]) > 0;
    }

    public function getPopular(int $limit = 20): array
    {
        return $this->orderBy('usage_count', 'DESC')
            ->limit($limit)
            ->get();
    }
}
