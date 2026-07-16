<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');

        $query = $db->table('pages');

        if ($search !== '') {
            $query = $query->where('title', 'LIKE', "%{$search}%");
        }

        $pages = $query->orderBy('created_at', 'DESC')->paginate(20, $page);

        return $this->view('admin.pages', [
            'title' => 'CMS Pages',
            'activeMenu' => 'pages',
            'pages' => $pages,
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        return $this->view('admin.page-form', [
            'title' => 'Create Page',
            'activeMenu' => 'pages',
            'page_data' => null,
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
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect('/admin/pages/create');
        }

        $db = Database::getInstance();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->request->input('slug', '')), '-'));

        $db->table('pages')->insert([
            'title' => $this->request->input('title', ''),
            'slug' => $slug,
            'content' => $this->request->input('content', ''),
            'meta_title' => $this->request->input('meta_title', ''),
            'meta_description' => $this->request->input('meta_description', ''),
            'is_published' => $this->request->input('is_published') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Page created.')->redirect('/admin/pages');
    }

    public function edit(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $pageData = $db->table('pages')->where('id', (int) $id)->first();

        if (!$pageData) {
            return $this->withError('Page not found.')->redirect('/admin/pages');
        }

        return $this->view('admin.page-form', [
            'title' => 'Edit Page',
            'activeMenu' => 'pages',
            'page_data' => $pageData,
        ]);
    }

    public function update(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->request->input('slug', '')), '-'));

        $db->table('pages')->where('id', (int) $id)->update([
            'title' => $this->request->input('title', ''),
            'slug' => $slug,
            'content' => $this->request->input('content', ''),
            'meta_title' => $this->request->input('meta_title', ''),
            'meta_description' => $this->request->input('meta_description', ''),
            'is_published' => $this->request->input('is_published') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Page updated.')->redirect('/admin/pages');
    }

    public function delete(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $db->table('pages')->where('id', (int) $id)->delete();

        return $this->withSuccess('Page deleted.')->redirect('/admin/pages');
    }
}
