<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');
        $role = $this->request->input('role', '');
        $status = $this->request->input('status', '');

        $query = $db->table('users');

        if ($search !== '') {
            $query = $query->where('username', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhere('display_name', 'LIKE', "%{$search}%");
        }

        if ($role !== '') {
            if ($role === 'admin') {
                $query = $query->where('is_admin', 1);
            } else {
                $query = $query->where('is_admin', 0);
            }
        }

        if ($status !== '') {
            if ($status === 'active') {
                $query = $query->where('status', 'active');
            } elseif ($status === 'banned') {
                $query = $query->where('status', 'banned');
            }
        }

        $users = $query->orderBy('created_at', 'DESC')->paginate(20, $page);

        return $this->view('admin.users', [
            'title' => 'User Management',
            'activeMenu' => 'users',
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
        ]);
    }

    public function create(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $roles = $db->table('roles')->orderBy('name', 'ASC')->get();

        return $this->view('admin.user-create', [
            'title' => 'Create User',
            'activeMenu' => 'users',
            'roles' => $roles,
        ]);
    }

    public function store(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'username' => 'required|min:3|max:255|alpha_dash',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'display_name' => 'required|max:255',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect('/admin/users/create');
        }

        $db = Database::getInstance();
        $data = $this->request->only([
            'username', 'email', 'password', 'display_name', 'description',
        ]);

        $existing = $db->table('users')->where('email', $data['email'])->first();
        if ($existing) {
            return $this->withInput()->withError('Email already exists.')->redirect('/admin/users/create');
        }

        $existing = $db->table('users')->where('username', $data['username'])->first();
        if ($existing) {
            return $this->withInput()->withError('Username already taken.')->redirect('/admin/users/create');
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['status'] = 'active';

        $db->table('users')->insert($data);

        $userId = $db->getPdo()->lastInsertId();
        $role = $this->request->input('role', '');
        if ($role !== '') {
            $db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => (int) $role,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($this->request->input('is_admin') === '1') {
            $db->table('users')->where('id', $userId)->update(['is_admin' => 1]);
        }

        return $this->withSuccess('User created successfully.')->redirect('/admin/users');
    }

    public function edit(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $user = $db->table('users')->where('id', (int) $id)->first();

        if (!$user) {
            return $this->withError('User not found.')->redirect('/admin/users');
        }

        $roles = $db->table('roles')->orderBy('name', 'ASC')->get();
        $userRoles = $db->table('user_roles')->where('user_id', (int) $id)->get();
        $userRoleIds = array_column($userRoles, 'role_id');

        return $this->view('admin.user-edit', [
            'title' => 'Edit User',
            'activeMenu' => 'users',
            'user' => $user,
            'roles' => $roles,
            'userRoleIds' => $userRoleIds,
        ]);
    }

    public function update(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $userId = (int) $id;

        $data = $this->request->only([
            'username', 'email', 'display_name', 'description',
        ]);

        $data['updated_at'] = date('Y-m-d H:i:s');

        $password = $this->request->input('password', '');
        if ($password !== '') {
            if (strlen($password) < 6) {
                return $this->withInput()->withError('Password must be at least 6 characters.')->redirect("/admin/users/edit/{$id}");
            }
            $data['password'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $db->table('users')->where('id', $userId)->update($data);

        $db->table('user_roles')->where('user_id', $userId)->delete();
        $role = $this->request->input('role', '');
        if ($role !== '') {
            $db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => (int) $role,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $isAdmin = $this->request->input('is_admin') === '1' ? 1 : 0;
        $db->table('users')->where('id', $userId)->update(['is_admin' => $isAdmin]);

        return $this->withSuccess('User updated successfully.')->redirect('/admin/users');
    }

    public function delete(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $db->table('users')->where('id', (int) $id)->update([
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('User deleted.')->redirect('/admin/users');
    }

    public function toggleStatus(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $db = Database::getInstance();
        $user = $db->table('users')->where('id', (int) $id)->first();

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $newStatus = ($user['status'] ?? 'active') === 'active' ? 'banned' : 'active';
        $db->table('users')->where('id', (int) $id)->update(['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->json(['success' => true, 'status' => $newStatus, 'message' => "User {$newStatus}."]);
    }
}
