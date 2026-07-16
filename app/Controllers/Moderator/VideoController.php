<?php

declare(strict_types=1);

namespace App\Controllers\Moderator;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class VideoController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');
        $status = $this->request->input('status', '');
        $perPage = 20;

        $query = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->whereNull('videos.deleted_at');

        if ($search !== '') {
            $query = $query->where('videos.title', 'LIKE', '%' . $search . '%');
        }

        if ($status !== '') {
            $query = $query->where('videos.status', $status);
        }

        $result = $query->orderBy('videos.created_at', 'DESC')
            ->paginate($perPage, $page);

        return $this->view('moderator.videos', [
            'title' => 'Manage Videos',
            'activeMenu' => 'videos',
            'videos' => $result['data'],
            'pagination' => $result,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function reported(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $db->table('reports')
            ->join('users', 'reports.reporter_id', '=', 'users.id')
            ->leftJoin('videos', 'reports.reportable_id', '=', 'videos.id')
            ->leftJoin('channels', 'videos.channel_id', '=', 'channels.id')
            ->where('reports.reportable_type', 'video')
            ->orderBy('reports.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('moderator.video-reports', [
            'title' => 'Reported Videos',
            'activeMenu' => 'video-reports',
            'reports' => $result['data'],
            'pagination' => $result,
        ]);
    }

    public function pending(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->where('videos.status', 'pending')
            ->whereNull('videos.deleted_at')
            ->orderBy('videos.created_at', 'ASC')
            ->paginate(20, $page);

        return $this->view('moderator.videos', [
            'title' => 'Pending Videos',
            'activeMenu' => 'videos',
            'videos' => $result['data'],
            'pagination' => $result,
            'search' => '',
            'status' => 'pending',
        ]);
    }

    public function action(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $errors = $this->validate([
            'video_id' => 'required|numeric',
            'action' => 'required|in:approve,reject,remove,strike,warn',
        ]);

        if (!empty($errors)) {
            return $this->json(['error' => 'Invalid input', 'errors' => $errors], 422);
        }

        $videoId = (int) $this->request->input('video_id');
        $action = $this->request->input('action');
        $reason = $this->request->input('reason', '');
        $userId = (int) $this->session->get('user_id');

        $db = Database::getInstance();
        $video = $db->table('videos')->find($videoId);

        if ($video === null) {
            return $this->json(['error' => 'Video not found'], 404);
        }

        match ($action) {
            'approve' => $db->table('videos')->where('id', $videoId)->update([
                'status' => 'published',
                'published_at' => date('Y-m-d H:i:s'),
            ]),
            'reject' => $db->table('videos')->where('id', $videoId)->update([
                'status' => 'rejected',
            ]),
            'remove' => $db->table('videos')->where('id', $videoId)->update([
                'status' => 'deleted',
                'deleted_at' => date('Y-m-d H:i:s'),
            ]),
            'strike' => $this->strikeChannel((int) $video['channel_id'], $userId, $reason, $videoId),
            'warn' => $this->warnCreator((int) $video['channel_id'], $userId, $reason, $videoId),
        };

        if ($action === 'approve' || $action === 'reject') {
            $db->table('reports')
                ->where('reportable_type', 'video')
                ->where('reportable_id', $videoId)
                ->where('status', 'pending')
                ->update([
                    'status' => 'resolved',
                    'reviewed_by' => $userId,
                    'reviewed_at' => date('Y-m-d H:i:s'),
                ]);
        }

        $db->table('violations')->insert([
            'video_id' => $videoId,
            'channel_id' => $video['channel_id'],
            'user_id' => $video['channel_id'],
            'type' => $action,
            'description' => $reason !== '' ? $reason : "Action {$action} taken on video #{$videoId}",
            'action_taken' => ucfirst($action) . ' video',
            'taken_by' => $userId,
        ]);

        if ($this->isApiRequest()) {
            return $this->json(['success' => true, 'message' => "Video action '{$action}' completed."]);
        }

        $this->withSuccess("Action '{$action}' completed on the video.");
        return $this->redirect('/moderator/videos');
    }

    private function strikeChannel(int $channelId, int $moderatorId, string $reason, int $videoId): void
    {
        $db = Database::getInstance();
        $db->table('violations')->insert([
            'video_id' => $videoId,
            'channel_id' => $channelId,
            'type' => 'channel_strike',
            'description' => $reason !== '' ? $reason : 'Channel strike issued',
            'action_taken' => 'Channel strike',
            'taken_by' => $moderatorId,
        ]);
    }

    private function warnCreator(int $channelId, int $moderatorId, string $reason, int $videoId): void
    {
        $db = Database::getInstance();
        $db->table('violations')->insert([
            'video_id' => $videoId,
            'channel_id' => $channelId,
            'type' => 'warning',
            'description' => $reason !== '' ? $reason : 'Warning issued to creator',
            'action_taken' => 'Warning',
            'taken_by' => $moderatorId,
        ]);
    }
}
