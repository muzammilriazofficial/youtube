<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $roles = $db->table('roles')
            ->leftJoin('role_permissions', 'roles.id', '=', 'role_permissions.role_id')
            ->select('roles.*', 'COUNT(role_permissions.permission_id) as permission_count')
            ->groupBy('roles.id')
            ->orderBy('roles.name', 'ASC')
            ->get();

        return $this->view('admin.roles', [
            'title' => 'Role Management',
            'activeMenu' => 'roles',
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        return $this->view('admin.role-form', [
            'title' => 'Create Role',
            'activeMenu' => 'roles',
            'role' => null,
            'permissions' => [],
        ]);
    }

    public function store(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'name' => 'required|max:255|alpha_dash',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect('/admin/roles/create');
        }

        $db = Database::getInstance();
        $name = $this->request->input('name', '');
        $description = $this->request->input('description', '');

        $existing = $db->table('roles')->where('name', $name)->first();
        if ($existing) {
            return $this->withInput()->withError('Role already exists.')->redirect('/admin/roles/create');
        }

        $db->table('roles')->insert([
            'name' => $name,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Role created.')->redirect('/admin/roles');
    }

    public function edit(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $role = $db->table('roles')->where('id', (int) $id)->first();

        if (!$role) {
            return $this->withError('Role not found.')->redirect('/admin/roles');
        }

        $permissions = $db->table('permissions')->orderBy('name', 'ASC')->get();
        $rolePermissions = $db->table('role_permissions')
            ->where('role_id', (int) $id)
            ->get();
        $assignedPermissionIds = array_column($rolePermissions, 'permission_id');

        return $this->view('admin.role-form', [
            'title' => 'Edit Role',
            'activeMenu' => 'roles',
            'role' => $role,
            'permissions' => $permissions,
            'assignedPermissionIds' => $assignedPermissionIds,
        ]);
    }

    public function update(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'name' => 'required|max:255|alpha_dash',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect("/admin/roles/edit/{$id}");
        }

        $db = Database::getInstance();
        $db->table('roles')->where('id', (int) $id)->update([
            'name' => $this->request->input('name', ''),
            'description' => $this->request->input('description', ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Role updated.')->redirect('/admin/roles');
    }

    public function delete(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $db->table('role_permissions')->where('role_id', (int) $id)->delete();
        $db->table('user_roles')->where('role_id', (int) $id)->delete();
        $db->table('roles')->where('id', (int) $id)->delete();

        return $this->withSuccess('Role deleted.')->redirect('/admin/roles');
    }

    public function assignPermissions(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $roleId = (int) $id;

        $db->table('role_permissions')->where('role_id', $roleId)->delete();

        $permissionIds = $this->request->input('permissions', []);
        if (!is_array($permissionIds)) {
            $permissionIds = [];
        }

        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($permissionIds as $permId) {
            $rows[] = [
                'role_id' => $roleId,
                'permission_id' => (int) $permId,
                'created_at' => $now,
            ];
        }

        if (!empty($rows)) {
            $db->table('role_permissions')->insertBatch($rows);
        }

        return $this->withSuccess('Permissions updated.')->redirect("/admin/roles/edit/{$id}");
    }
}
