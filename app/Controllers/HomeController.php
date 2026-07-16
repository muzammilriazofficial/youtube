<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Video;
use App\Models\Category;
use App\Models\Channel;
use App\Models\Subscription;
use App\Models\WatchHistory;

class HomeController extends Controller
{
    public function index(): Response
    {
        $videoModel = new Video();
        $categoryModel = new Category();
        $channelModel = new Channel();

        $featuredVideos = $videoModel->db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->where('videos.visibility', 'public')
            ->where('videos.status', 'published')
            ->whereNull('videos.deleted_at')
            ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
            ->orderBy('videos.view_count', 'DESC')
            ->limit(10)
            ->get();

        $trendingVideos = $videoModel->getTrendingVideos(12);

        $latestVideos = $videoModel->db->table('videos')
            ->join('channels', 'videos.channel_id', '=', 'channels.id')
            ->where('videos.visibility', 'public')
            ->where('videos.status', 'published')
            ->whereNull('videos.deleted_at')
            ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
            ->orderBy('videos.published_at', 'DESC')
            ->limit(20)
            ->get();

        $categories = $categoryModel->getActive();

        $topChannels = $channelModel->orderBy('subscriber_count', 'DESC')
            ->limit(10)
            ->get();

        $data = [
            'title' => 'Home',
            'featuredVideos' => $featuredVideos,
            'trendingVideos' => $trendingVideos,
            'latestVideos' => $latestVideos,
            'categories' => $categories,
            'topChannels' => $topChannels,
            'subscriptionVideos' => [],
            'continueWatching' => [],
        ];

        if ($this->session->isAuthenticated()) {
            $userId = (int) $this->session->get('user_id');
            $subscriptionModel = new Subscription();
            $historyModel = new WatchHistory();

            $subscriptions = $subscriptionModel->getSubscriberChannels($userId, 50);
            $channelIds = array_column($subscriptions, 'channel_id');

            if (!empty($channelIds)) {
                $data['subscriptionVideos'] = $videoModel->db->table('videos')
                    ->join('channels', 'videos.channel_id', '=', 'channels.id')
                    ->whereIn('videos.channel_id', $channelIds)
                    ->where('videos.visibility', 'public')
                    ->where('videos.status', 'published')
                    ->whereNull('videos.deleted_at')
                    ->select('videos.*', 'channels.name as channel_name', 'channels.slug as channel_slug', 'channels.custom_url as channel_custom_url', 'channels.avatar as channel_avatar')
                    ->orderBy('videos.published_at', 'DESC')
                    ->limit(20)
                    ->get();
            }

            $data['continueWatching'] = $historyModel->getContinueWatching($userId, 12);
        }

        return $this->view('guest.home', $data);
    }
}
