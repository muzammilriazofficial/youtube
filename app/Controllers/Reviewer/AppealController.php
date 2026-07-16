<?php

declare(strict_types=1);

namespace App\Controllers\Reviewer;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class AppealController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $db->table('reports')
            ->join('users', 'reports.reporter_id', '=', 'users.id')
            ->where('reports.status', 'pending')
            ->orderBy('reports.created_at', 'ASC')
            ->paginate(20, $page);

        return $this->view('reviewer.appeals', [
            'title' => 'Appeals',
            'activeMenu' => 'appeals',
            'appeals' => $result['data'],
            'pagination' => $result,
        ]);
    }

    public function resolve(string $id): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $errors = $this->validate([
            'decision' => 'required|in:uphold,overturn',
            'notes' => 'max:1000',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $appealId = (int) $id;
        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $appeal = $db->table('reports')->find($appealId);
        if ($appeal === null) {
            return $this->respondWithError('Appeal not found.', 404);
        }

        $decision = $this->request->input('decision');
        $newStatus = $decision === 'uphold' ? 'resolved' : 'dismissed';

        $db->table('reports')->where('id', $appealId)->update([
            'status' => $newStatus,
            'reviewed_by' => $userId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'resolution' => ($decision === 'uphold' ? 'Upheld: ' : 'Overturned: ') . $this->request->input('notes', ''),
        ]);

        $this->withSuccess("Appeal {$decision}d successfully.");
        return $this->redirect('/reviewer/appeals');
    }
}
