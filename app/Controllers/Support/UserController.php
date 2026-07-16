<?php

declare(strict_types=1);

namespace App\Controllers\Support;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = (int) ($this->request->input('page', 1));
        $status = $this->request->input('status', '');
        $role = $this->request->input('role', '');
        $search = $this->request->input('search', '');

        $query = $db->table('users');

        if ($status !== '') {
            if ($status === 'banned') {
                $query = $query->where('is_banned', 1);
            } elseif ($status === 'active') {
                $query = $query->where('is_banned', 0);
            }
        }

        if ($role !== '') {
            $query = $query->join('role_user', 'users.id', '=', 'role_user.user_id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('roles.name', $role);
        }

        if ($search !== '') {
            $query = $query->where('username', 'LIKE', "%{$search}%");
        }

        $users = $query->orderBy('users.created_at', 'DESC')->paginate(20, $page);

        return $this->view('support.users', [
            'title' => 'User Management',
            'activeMenu' => 'users',
            'users' => $users,
            'filters' => [
                'status' => $status,
                'role' => $role,
                'search' => $search,
            ],
        ]);
    }

    public function show(int $id): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $user = $db->table('users')->find($id);

        if ($user === null) {
            return $this->redirect('/support/users');
        }

        $videoCount = $db->table('videos')->where('user_id', $id)->count();

        $commentCount = $db->table('comments')->where('user_id', $id)->count();

        $reportsReceived = $db->table('reports')
            ->where('reportable_type', 'user')
            ->where('reportable_id', $id)
            ->count();

        $reportsFiled = $db->table('reports')->where('reporter_id', $id)->count();

        $tickets = $db->table('support_tickets')
            ->where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get();

        $channels = $db->table('channels')
            ->where('user_id', $id)
            ->get();

        $recentVideos = $db->table('videos')
            ->where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        $recentComments = $db->table('comments')
            ->where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        return $this->view('support.user-show', [
            'title' => 'User: ' . $user['username'],
            'activeMenu' => 'users',
            'user' => $user,
            'videoCount' => $videoCount,
            'commentCount' => $commentCount,
            'reportsReceived' => $reportsReceived,
            'reportsFiled' => $reportsFiled,
            'tickets' => $tickets,
            'channels' => $channels,
            'recentVideos' => $recentVideos,
            'recentComments' => $recentComments,
        ]);
    }
}
