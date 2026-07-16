<?php

declare(strict_types=1);

namespace App\Controllers\Moderator;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class ChannelController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('moderator', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');
        $perPage = 20;

        $query = $db->table('channels')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->whereNull('channels.deleted_at');

        if ($search !== '') {
            $query = $query->where('channels.name', 'LIKE', '%' . $search . '%');
        }

        $result = $query->orderBy('channels.created_at', 'DESC')
            ->paginate($perPage, $page);

        return $this->view('moderator.channels', [
            'title' => 'Manage Channels',
            'activeMenu' => 'channels',
            'channels' => $result['data'],
            'pagination' => $result,
            'search' => $search,
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
            ->leftJoin('channels', 'reports.reportable_id', '=', 'channels.id')
            ->leftJoin('users AS channel_user', 'channels.user_id', '=', 'channel_user.id')
            ->where('reports.reportable_type', 'channel')
            ->orderBy('reports.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('moderator.channel-reports', [
            'title' => 'Reported Channels',
            'activeMenu' => 'channel-reports',
            'reports' => $result['data'],
            'pagination' => $result,
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
            'channel_id' => 'required|numeric',
            'action' => 'required|in:verify,unverify,warn,suspend,ban',
        ]);

        if (!empty($errors)) {
            return $this->json(['error' => 'Invalid input', 'errors' => $errors], 422);
        }

        $channelId = (int) $this->request->input('channel_id');
        $action = $this->request->input('action');
        $reason = $this->request->input('reason', '');
        $userId = (int) $this->session->get('user_id');

        $db = Database::getInstance();
        $channel = $db->table('channels')->find($channelId);

        if ($channel === null) {
            return $this->json(['error' => 'Channel not found'], 404);
        }

        match ($action) {
            'verify' => $db->table('channels')->where('id', $channelId)->update([
                'is_verified' => 1,
            ]),
            'unverify' => $db->table('channels')->where('id', $channelId)->update([
                'is_verified' => 0,
            ]),
            'suspend' => $db->table('channels')->where('id', $channelId)->update([
                'is_partner' => 0,
            ]),
            'ban' => $db->table('users')->where('id', $channel['user_id'])->update([
                'is_banned' => 1,
                'ban_reason' => $reason !== '' ? $reason : 'Banned by moderator',
            ]),
            'warn' => null,
        };

        $db->table('violations')->insert([
            'channel_id' => $channelId,
            'user_id' => $channel['user_id'],
            'type' => $action,
            'description' => $reason !== '' ? $reason : "Action {$action} taken on channel #{$channelId}",
            'action_taken' => ucfirst($action) . ' channel',
            'taken_by' => $userId,
        ]);

        $db->table('reports')
            ->where('reportable_type', 'channel')
            ->where('reportable_id', $channelId)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'reviewed_by' => $userId,
                'reviewed_at' => date('Y-m-d H:i:s'),
            ]);

        if ($this->isApiRequest()) {
            return $this->json(['success' => true, 'message' => "Channel action '{$action}' completed."]);
        }

        $this->withSuccess("Action '{$action}' completed on the channel.");
        return $this->redirect('/moderator/channels');
    }
}
