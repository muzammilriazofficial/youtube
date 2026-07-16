<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected string $table = 'categories';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [
        'is_active' => 'boolean',
    ];

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function getActive(): array
    {
        return $this->where('is_active', true)
            ->orderBy('sort_order', 'ASC')
            ->get();
    }

    public function getChildren(int $parentId): array
    {
        return $this->where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('sort_order', 'ASC')
            ->get();
    }

    public function getVideos(int $categoryId, int $limit = 20): array
    {
        return $this->db->table('videos')
            ->where('category_id', $categoryId)
            ->where('visibility', 'public')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }

    public function getRootCategories(): array
    {
        return $this->where('parent_id', null)
            ->where('is_active', true)
            ->orderBy('sort_order', 'ASC')
            ->get();
    }
}
