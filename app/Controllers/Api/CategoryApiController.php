<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Response;
use App\Models\Category;
use App\Models\Video;

class CategoryApiController extends ApiController
{
    public function index(): Response
    {
        $categoryModel = new Category();
        $categories    = $categoryModel->getActive();

        return $this->success($categories, 'Categories retrieved.');
    }

    public function show(string $id): Response
    {
        $categoryModel = new Category();
        $category      = $categoryModel->find((int) $id);

        if ($category === null) {
            return $this->error('Category not found.', 404);
        }

        $page  = $this->getPage();
        $limit = $this->getPerPage();

        $videoModel = new Video();
        $videos = $videoModel->where('category_id', (int) $id)
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->paginate($limit, $page);

        $category['videos'] = $videos['data'] ?? [];
        $category['meta'] = [
            'total'        => $videos['total'] ?? 0,
            'current_page' => $videos['current_page'] ?? $page,
            'last_page'    => $videos['last_page'] ?? 1,
            'per_page'     => $videos['per_page'] ?? $limit,
        ];

        return $this->success($category);
    }
}
