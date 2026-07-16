<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class AuditLogController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $event = $this->request->input('event', '');
        $userId = $this->request->input('user_id', '');

        $query = $db->table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id');

        if ($event !== '') {
            $query = $query->where('audit_logs.action', $event);
        }

        if ($userId !== '') {
            $query = $query->where('audit_logs.user_id', (int) $userId);
        }

        $logs = $query->select('audit_logs.*', 'users.username')
            ->orderBy('audit_logs.created_at', 'DESC')
            ->paginate(30, $page);

        $events = $db->table('audit_logs')
            ->select('action')
            ->groupBy('action')
            ->get();

        return $this->view('admin.audit-logs', [
            'title' => 'Audit Logs',
            'activeMenu' => 'audit-logs',
            'logs' => $logs,
            'events' => array_column($events, 'action'),
            'event' => $event,
            'userId' => $userId,
        ]);
    }
}
