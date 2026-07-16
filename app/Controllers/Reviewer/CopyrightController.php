<?php

declare(strict_types=1);

namespace App\Controllers\Reviewer;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class CopyrightController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $status = $this->request->input('status', '');

        $query = $db->table('copyright_claims')
            ->join('videos', 'copyright_claims.video_id', '=', 'videos.id')
            ->join('channels', 'videos.channel_id', '=', 'channels.id');

        if ($status !== '') {
            $query = $query->where('copyright_claims.status', $status);
        }

        $result = $query->orderBy('copyright_claims.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('reviewer.copyright', [
            'title' => 'Copyright Claims',
            'activeMenu' => 'copyright',
            'claims' => $result['data'],
            'pagination' => $result,
            'status' => $status,
        ]);
    }

    public function claims(): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $db->table('copyright_claims')
            ->join('videos', 'copyright_claims.video_id', '=', 'videos.id')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->orderBy('copyright_claims.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('reviewer.copyright-claims', [
            'title' => 'Copyright Claims Detail',
            'activeMenu' => 'copyright',
            'claims' => $result['data'],
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
            'resolution' => 'required|in:accepted,rejected,counter_notified',
            'notes' => 'max:1000',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $claimId = (int) $id;
        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $claim = $db->table('copyright_claims')->find($claimId);
        if ($claim === null) {
            return $this->respondWithError('Claim not found.', 404);
        }

        $status = $this->request->input('resolution');
        $db->table('copyright_claims')->where('id', $claimId)->update([
            'status' => $status,
            'reviewed_by' => $userId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'resolution' => $this->request->input('notes', ''),
        ]);

        if ($status === 'accepted') {
            $db->table('videos')->where('id', $claim['video_id'])->update([
                'copyright_status' => 'claimed',
            ]);
        } elseif ($status === 'rejected') {
            $db->table('videos')->where('id', $claim['video_id'])->update([
                'copyright_status' => 'clean',
            ]);
        }

        $this->withSuccess('Copyright claim resolved.');
        return $this->redirect('/reviewer/copyright');
    }
}
