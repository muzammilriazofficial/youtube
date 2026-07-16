<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class TagController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');

        $query = $db->table('tags')
            ->leftJoin('video_tags', 'tags.id', '=', 'video_tags.tag_id')
            ->select('tags.*', 'COUNT(video_tags.video_id) as usage_count')
            ->groupBy('tags.id');

        if ($search !== '') {
            $query = $query->where('tags.name', 'LIKE', "%{$search}%");
        }

        $tags = $query->orderBy('usage_count', 'DESC')->paginate(20, $page);

        return $this->view('admin.tags', [
            'title' => 'Tag Management',
            'activeMenu' => 'tags',
            'tags' => $tags,
            'search' => $search,
        ]);
    }

    public function store(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $db = Database::getInstance();
        $name = trim($this->request->input('name', ''));

        if ($name === '') {
            return $this->json(['error' => 'Tag name is required.'], 422);
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $existing = $db->table('tags')->where('slug', $slug)->first();
        if ($existing) {
            return $this->json(['error' => 'Tag already exists.'], 422);
        }

        $db->table('tags')->insert([
            'name' => $name,
            'slug' => $slug,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->json(['success' => true, 'message' => 'Tag created.']);
    }

    public function delete(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $db->table('video_tags')->where('tag_id', (int) $id)->delete();
        $db->table('tags')->where('id', (int) $id)->delete();

        return $this->withSuccess('Tag deleted.')->redirect('/admin/tags');
    }
}
