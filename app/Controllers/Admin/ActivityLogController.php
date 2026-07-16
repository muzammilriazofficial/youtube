<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class ActivityLogController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $action = $this->request->input('action', '');
        $userId = $this->request->input('user_id', '');

        $query = $db->table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id');

        if ($action !== '') {
            $query = $query->where('activity_logs.action', $action);
        }

        if ($userId !== '') {
            $query = $query->where('activity_logs.user_id', (int) $userId);
        }

        $logs = $query->select('activity_logs.*', 'users.username')
            ->orderBy('activity_logs.created_at', 'DESC')
            ->paginate(30, $page);

        $actions = $db->table('activity_logs')
            ->select('action')
            ->groupBy('action')
            ->get();

        return $this->view('admin.activity-logs', [
            'title' => 'Activity Logs',
            'activeMenu' => 'activity-logs',
            'logs' => $logs,
            'actions' => array_column($actions, 'action'),
            'action' => $action,
            'userId' => $userId,
        ]);
    }
}
