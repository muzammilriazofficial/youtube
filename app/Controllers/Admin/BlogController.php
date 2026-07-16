<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');

        $query = $db->table('blog_posts')
            ->leftJoin('users', 'blog_posts.author_id', '=', 'users.id');

        if ($search !== '') {
            $query = $query->where('blog_posts.title', 'LIKE', "%{$search}%");
        }

        $posts = $query->select('blog_posts.*', 'users.username as author_name')
            ->orderBy('blog_posts.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.blog', [
            'title' => 'Blog Management',
            'activeMenu' => 'blog',
            'posts' => $posts,
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        return $this->view('admin.blog-form', [
            'title' => 'Create Blog Post',
            'activeMenu' => 'blog',
            'post' => null,
        ]);
    }

    public function store(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'title' => 'required|max:255',
            'slug' => 'required|max:255',
            'content' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect('/admin/blog/create');
        }

        $db = Database::getInstance();
        $userId = (int) $this->session->get('user_id');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->request->input('slug', '')), '-'));

        $db->table('blog_posts')->insert([
            'title' => $this->request->input('title', ''),
            'slug' => $slug,
            'content' => $this->request->input('content', ''),
            'excerpt' => $this->request->input('excerpt', ''),
            'author_id' => $userId,
            'status' => $this->request->input('status', 'draft'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Blog post created.')->redirect('/admin/blog');
    }

    public function edit(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $post = $db->table('blog_posts')->where('id', (int) $id)->first();

        if (!$post) {
            return $this->withError('Blog post not found.')->redirect('/admin/blog');
        }

        return $this->view('admin.blog-form', [
            'title' => 'Edit Blog Post',
            'activeMenu' => 'blog',
            'post' => $post,
        ]);
    }

    public function update(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->request->input('slug', '')), '-'));

        $db->table('blog_posts')->where('id', (int) $id)->update([
            'title' => $this->request->input('title', ''),
            'slug' => $slug,
            'content' => $this->request->input('content', ''),
            'excerpt' => $this->request->input('excerpt', ''),
            'status' => $this->request->input('status', 'draft'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Blog post updated.')->redirect('/admin/blog');
    }

    public function delete(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $db->table('blog_posts')->where('id', (int) $id)->delete();

        return $this->withSuccess('Blog post deleted.')->redirect('/admin/blog');
    }
}
