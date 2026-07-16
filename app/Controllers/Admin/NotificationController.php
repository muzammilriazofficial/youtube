<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $notifications = $db->table('notifications')
            ->join('users', 'notifications.user_id', '=', 'users.id')
            ->select('notifications.*', 'users.username')
            ->orderBy('notifications.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.notifications', [
            'title' => 'Notification Management',
            'activeMenu' => 'notifications',
            'notifications' => $notifications,
        ]);
    }

    public function send(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $roles = $db->table('roles')->orderBy('name', 'ASC')->get();

        return $this->view('admin.notification-send', [
            'title' => 'Send Broadcast Notification',
            'activeMenu' => 'notifications',
            'roles' => $roles,
        ]);
    }

    public function sendStore(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $errors = $this->validate([
            'title' => 'required|max:255',
            'message' => 'required',
            'target' => 'required|in:all,role',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->withError('Validation failed.')->redirect('/admin/notifications/send');
        }

        $db = Database::getInstance();
        $title = $this->request->input('title', '');
        $message = $this->request->input('message', '');
        $target = $this->request->input('target', 'all');

        if ($target === 'all') {
            $users = $db->table('users')->where('status', 'active')->get();
        } else {
            $roleId = (int) $this->request->input('role_id', 0);
            $userIds = array_column(
                $db->table('user_roles')->where('role_id', $roleId)->get(),
                'user_id'
            );
            $users = !empty($userIds) ? $db->table('users')->whereIn('id', $userIds)->where('status', 'active')->get() : [];
        }

        $now = date('Y-m-d H:i:s');
        $notifications = [];
        foreach ($users as $u) {
            $notifications[] = [
                'user_id' => $u['id'],
                'type' => 'admin_broadcast',
                'title' => $title,
                'message' => $message,
                'is_read' => 0,
                'created_at' => $now,
            ];
        }

        if (!empty($notifications)) {
            $db->table('notifications')->insertBatch($notifications);
        }

        return $this->withSuccess('Notification sent to ' . count($users) . ' users.')->redirect('/admin/notifications');
    }
}
