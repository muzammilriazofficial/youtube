<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Channel;
use App\Models\Video;
use App\Models\Playlist;

class ChannelController extends Controller
{
    public function index(): Response
    {
        $channelModel = new Channel();
        $page = (int) $this->request->query('page', 1);
        $sort = $this->request->query('sort', 'popular');

        $sortMap = [
            'popular' => ['subscriber_count', 'DESC'],
            'newest' => ['created_at', 'DESC'],
            'az' => ['name', 'ASC'],
        ];
        [$sortCol, $sortDir] = $sortMap[$sort] ?? $sortMap['popular'];

        $channels = $channelModel->orderBy($sortCol, $sortDir)->paginate(24, $page);

        return $this->view('guest.channels', [
            'title' => 'Channels',
            'channels' => $channels,
            'currentSort' => $sort,
        ]);
    }

    public function show(string $username): Response
    {
        $channelModel = new Channel();
        $channel = null;

        if (!empty($username)) {
            $channel = $channelModel->where('custom_url', $username)->first();
        }

        if ($channel === null) {
            $channel = $channelModel->findBySlug($username);
        }

        if ($channel === null) {
            $userModel = new \App\Models\User();
            $user = $userModel->findByUsername($username);
            if ($user) {
                $channel = $channelModel->findByUserId((int) $user['id']);
            }
        }

        if ($channel === null) {
            return $this->view('errors.404', ['title' => 'Not Found'], 404);
        }

        $tab = $this->request->query('tab', 'videos');
        $page = (int) $this->request->query('page', 1);

        $videoModel = new Video();
        $playlistModel = new Playlist();

        $videos = [];
        $playlists = [];

        if ($tab === 'videos' || $tab === '') {
            $videos = $videoModel->where('channel_id', (int) $channel['id'])
                ->where('visibility', 'public')
                ->where('status', 'published')
                ->orderBy('published_at', 'DESC')
                ->paginate(24, $page);
        } elseif ($tab === 'playlists') {
            $playlists = $playlistModel->where('user_id', (int) $channel['user_id'])
                ->where('visibility', 'public')
                ->orderBy('updated_at', 'DESC')
                ->paginate(24, $page);
        }

        $totalVideos = $videoModel->where('channel_id', (int) $channel['id'])
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->count();

        $totalViews = $videoModel->db->table('videos')
            ->where('channel_id', (int) $channel['id'])
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->sum('view_count');

        $isSubscribed = false;
        if ($this->isAuthenticated()) {
            $userId = (int) $this->session->get('user_id');
            $subModel = new \App\Models\Subscription();
            $isSubscribed = $subModel->isSubscribed($userId, (int) $channel['id']);
        }

        return $this->view('guest.channel-show', [
            'title' => $channel['name'],
            'channel' => $channel,
            'videos' => $videos,
            'playlists' => $playlists,
            'totalVideos' => $totalVideos,
            'currentTab' => $tab,
            'isSubscribed' => $isSubscribed,
        ]);
    }
}
