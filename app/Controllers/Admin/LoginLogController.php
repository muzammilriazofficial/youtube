<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class LoginLogController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $status = $this->request->input('status', '');
        $userId = $this->request->input('user_id', '');

        $query = $db->table('login_logs')
            ->leftJoin('users', 'login_logs.user_id', '=', 'users.id');

        if ($status !== '') {
            $query = $query->where('login_logs.status', $status);
        }

        if ($userId !== '') {
            $query = $query->where('login_logs.user_id', (int) $userId);
        }

        $logs = $query->select('login_logs.*', 'users.username', 'users.email')
            ->orderBy('login_logs.created_at', 'DESC')
            ->paginate(30, $page);

        return $this->view('admin.login-logs', [
            'title' => 'Login Logs',
            'activeMenu' => 'login-logs',
            'logs' => $logs,
            'status' => $status,
            'userId' => $userId,
        ]);
    }
}
