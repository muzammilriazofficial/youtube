<?php

declare(strict_types=1);

namespace App\Controllers\Viewer;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Subscription;
use App\Models\Channel;

class SubscriptionController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $subscriptionModel = new Subscription();
        $page = (int) $this->request->query('page', 1);

        $subscriptions = $subscriptionModel->getSubscriberChannels($userId, 100);

        $videoModel = new \App\Models\Video();
        $channelIds = array_column($subscriptions, 'channel_id');
        $latestVideos = [];
        if (!empty($channelIds)) {
            $latestVideos = $videoModel->db->table('videos')
                ->whereIn('channel_id', $channelIds)
                ->where('visibility', 'public')
                ->where('status', 'published')
                ->whereNull('deleted_at')
                ->orderBy('published_at', 'DESC')
                ->paginate(30, $page);
        }

        return $this->view('viewer.subscriptions', [
            'title' => 'Subscriptions',
            'subscriptions' => $subscriptions,
            'latestVideos' => $latestVideos,
            'subscriptionCount' => count($subscriptions),
        ]);
    }

    public function toggle(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelId = (int) $this->request->input('channel_id');

        $subscriptionModel = new Subscription();
        $channelModel = new Channel();

        $isSubscribed = $subscriptionModel->isSubscribed($userId, $channelId);

        if ($isSubscribed) {
            $subscriptionModel->db->table('subscriptions')
                ->where('subscriber_id', $userId)
                ->where('channel_id', $channelId)
                ->delete();
            $channelModel->decrementSubscribers($channelId);
            $status = 'unsubscribed';
        } else {
            $subscriptionModel->create([
                'subscriber_id' => $userId,
                'channel_id' => $channelId,
            ]);
            $channelModel->incrementSubscribers($channelId);
            $status = 'subscribed';
        }

        if ($this->request->expectsJson()) {
            return $this->json(['status' => $status]);
        }

        return $this->back();
    }
}
