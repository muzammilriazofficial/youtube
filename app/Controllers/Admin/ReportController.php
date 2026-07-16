<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $status = $this->request->input('status', '');
        $type = $this->request->input('type', '');

        $query = $db->table('reports')
            ->join('users', 'reports.reporter_id', '=', 'users.id');

        if ($status !== '') {
            $query = $query->where('reports.status', $status);
        }

        if ($type !== '') {
            $query = $query->where('reports.reportable_type', $type);
        }

        $reports = $query->select('reports.*', 'users.username as reporter_username')
            ->orderBy('reports.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.reports', [
            'title' => 'Report Management',
            'activeMenu' => 'reports',
            'reports' => $reports,
            'status' => $status,
            'type' => $type,
        ]);
    }

    public function show(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $report = $db->table('reports')
            ->join('users', 'reports.reporter_id', '=', 'users.id')
            ->where('reports.id', (int) $id)
            ->select('reports.*', 'users.username as reporter_username')
            ->first();

        if (!$report) {
            return $this->withError('Report not found.')->redirect('/admin/reports');
        }

        $reportable = null;
        $reportableType = $report['reportable_type'] ?? '';
        $reportableId = $report['reportable_id'] ?? 0;

        if ($reportableType === 'video') {
            $reportable = $db->table('videos')->where('id', (int) $reportableId)->first();
        } elseif ($reportableType === 'comment') {
            $reportable = $db->table('comments')
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->where('comments.id', (int) $reportableId)
                ->select('comments.*', 'users.username')
                ->first();
        } elseif ($reportableType === 'channel') {
            $reportable = $db->table('channels')->where('id', (int) $reportableId)->first();
        } elseif ($reportableType === 'user') {
            $reportable = $db->table('users')->where('id', (int) $reportableId)->first();
        }

        return $this->view('admin.report-show', [
            'title' => 'Report Detail',
            'activeMenu' => 'reports',
            'report' => $report,
            'reportable' => $reportable,
        ]);
    }

    public function resolve(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $reportId = (int) $id;
        $status = $this->request->input('status', 'resolved');
        $notes = $this->request->input('notes', '');

        $db->table('reports')->where('id', $reportId)->update([
            'status' => $status,
            'admin_notes' => $notes,
            'resolved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Report resolved.')->redirect('/admin/reports');
    }
}
