<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class PermissionController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $permissions = $db->table('permissions')
            ->orderBy('group_name', 'ASC')
            ->orderBy('name', 'ASC')
            ->get();

        $grouped = [];
        foreach ($permissions as $p) {
            $group = $p['group_name'] ?? 'general';
            $grouped[$group][] = $p;
        }

        return $this->view('admin.permissions', [
            'title' => 'Permission Management',
            'activeMenu' => 'permissions',
            'grouped' => $grouped,
        ]);
    }

    public function store(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'name' => 'required|max:255|alpha_dash',
            'group_name' => 'required|max:255',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect('/admin/permissions');
        }

        $db = Database::getInstance();
        $name = $this->request->input('name', '');
        $group = $this->request->input('group_name', 'general');
        $description = $this->request->input('description', '');

        $existing = $db->table('permissions')->where('name', $name)->first();
        if ($existing) {
            return $this->withInput()->withError('Permission already exists.')->redirect('/admin/permissions');
        }

        $db->table('permissions')->insert([
            'name' => $name,
            'group_name' => $group,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Permission created.')->redirect('/admin/permissions');
    }

    public function delete(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $db->table('role_permissions')->where('permission_id', (int) $id)->delete();
        $db->table('permissions')->where('id', (int) $id)->delete();

        return $this->withSuccess('Permission deleted.')->redirect('/admin/permissions');
    }
}
