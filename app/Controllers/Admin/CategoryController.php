<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');

        $query = $db->table('categories');

        if ($search !== '') {
            $query = $query->where('name', 'LIKE', "%{$search}%");
        }

        $categories = $query->orderBy('name', 'ASC')->paginate(20, $page);

        return $this->view('admin.categories', [
            'title' => 'Category Management',
            'activeMenu' => 'categories',
            'categories' => $categories,
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $parentCategories = $db->table('categories')
            ->whereNull('parent_id')
            ->orderBy('name', 'ASC')
            ->get();

        return $this->view('admin.category-form', [
            'title' => 'Create Category',
            'activeMenu' => 'categories',
            'category' => null,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'name' => 'required|max:255',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect('/admin/categories/create');
        }

        $db = Database::getInstance();
        $name = $this->request->input('name', '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $existing = $db->table('categories')->where('slug', $slug)->first();
        if ($existing) {
            $slug .= '-' . uniqid();
        }

        $parentId = $this->request->input('parent_id', null);
        if ($parentId === '' || $parentId === '0') {
            $parentId = null;
        }

        $db->table('categories')->insert([
            'name' => $name,
            'slug' => $slug,
            'description' => $this->request->input('description', ''),
            'parent_id' => $parentId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Category created.')->redirect('/admin/categories');
    }

    public function edit(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $category = $db->table('categories')->where('id', (int) $id)->first();

        if (!$category) {
            return $this->withError('Category not found.')->redirect('/admin/categories');
        }

        $parentCategories = $db->table('categories')
            ->whereNull('parent_id')
            ->where('id', '!=', (int) $id)
            ->orderBy('name', 'ASC')
            ->get();

        return $this->view('admin.category-form', [
            'title' => 'Edit Category',
            'activeMenu' => 'categories',
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'name' => 'required|max:255',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect("/admin/categories/edit/{$id}");
        }

        $db = Database::getInstance();
        $name = $this->request->input('name', '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

        $parentId = $this->request->input('parent_id', null);
        if ($parentId === '' || $parentId === '0') {
            $parentId = null;
        }

        $db->table('categories')->where('id', (int) $id)->update([
            'name' => $name,
            'slug' => $slug,
            'description' => $this->request->input('description', ''),
            'parent_id' => $parentId,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Category updated.')->redirect('/admin/categories');
    }

    public function delete(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $db->table('categories')->where('id', (int) $id)->delete();

        return $this->withSuccess('Category deleted.')->redirect('/admin/categories');
    }
}
