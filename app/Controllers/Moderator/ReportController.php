<?php

declare(strict_types=1);

namespace App\Controllers\Moderator;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $status = $this->request->input('status', '');
        $type = $this->request->input('type', '');
        $perPage = 20;

        $query = $db->table('reports')
            ->join('users', 'reports.reporter_id', '=', 'users.id');

        if ($status !== '') {
            $query = $query->where('reports.status', $status);
        }

        if ($type !== '') {
            $query = $query->where('reports.reportable_type', $type);
        }

        $result = $query->orderBy('reports.created_at', 'DESC')
            ->paginate($perPage, $page);

        return $this->view('moderator.reports', [
            'title' => 'All Reports',
            'activeMenu' => 'reports',
            'reports' => $result['data'],
            'pagination' => $result,
            'status' => $status,
            'type' => $type,
        ]);
    }

    public function show(string $id): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $report = $db->table('reports')
            ->join('users', 'reports.reporter_id', '=', 'users.id')
            ->where('reports.id', (int) $id)
            ->first();

        if ($report === null) {
            $this->withError('Report not found.');
            return $this->redirect('/moderator/reports');
        }

        $reportedContent = null;

        match ($report['reportable_type']) {
            'video' => $reportedContent = $db->table('videos')
                ->join('channels', 'videos.channel_id', '=', 'channels.id')
                ->where('videos.id', $report['reportable_id'])
                ->first(),
            'comment' => $reportedContent = $db->table('comments')
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->join('videos', 'comments.video_id', '=', 'videos.id')
                ->where('comments.id', $report['reportable_id'])
                ->first(),
            'channel' => $reportedContent = $db->table('channels')
                ->join('users', 'channels.user_id', '=', 'users.id')
                ->where('channels.id', $report['reportable_id'])
                ->first(),
            default => null,
        };

        return $this->view('moderator.report-show', [
            'title' => 'Report Details',
            'activeMenu' => 'reports',
            'report' => $report,
            'reportedContent' => $reportedContent,
        ]);
    }

    public function resolve(string $id): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $errors = $this->validate([
            'resolution' => 'required',
            'status' => 'required|in:resolved,dismissed',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $reportId = (int) $id;
        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $report = $db->table('reports')->find($reportId);
        if ($report === null) {
            return $this->respondWithError('Report not found.', 404);
        }

        $db->table('reports')->where('id', $reportId)->update([
            'status' => $this->request->input('status', 'resolved'),
            'reviewed_by' => $userId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'resolution' => $this->request->input('resolution'),
        ]);

        $this->withSuccess('Report resolved successfully.');
        return $this->redirect('/moderator/reports');
    }
}
