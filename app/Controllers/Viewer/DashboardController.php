<?php

declare(strict_types=1);

namespace App\Controllers\Viewer;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Subscription;
use App\Models\WatchHistory;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $subscriptionModel = new Subscription();
        $videoModel = new Video();
        $historyModel = new WatchHistory();

        $subscriptions = $subscriptionModel->getSubscriberChannels($userId, 50);
        $channelIds = array_column($subscriptions, 'channel_id');

        $subscriptionVideos = [];
        if (!empty($channelIds)) {
            $subscriptionVideos = $videoModel->db->table('videos')
                ->whereIn('channel_id', $channelIds)
                ->where('visibility', 'public')
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->orderBy('published_at', 'DESC')
                ->limit(20)
                ->get();
        }

        $history = $historyModel->getUserHistory($userId, 12);
        $continueWatching = $historyModel->getContinueWatching($userId, 12);

        $recommendations = $videoModel->where('visibility', 'public')
            ->where('status', 'published')
            ->orderBy('view_count', 'DESC')
            ->limit(20)
            ->get();

        return $this->view('viewer.dashboard', [
            'title' => 'Dashboard',
            'subscriptionVideos' => $subscriptionVideos,
            'history' => $history,
            'continueWatching' => $continueWatching,
            'recommendations' => $recommendations,
            'subscriptionCount' => count($channelIds),
        ]);
    }
}
