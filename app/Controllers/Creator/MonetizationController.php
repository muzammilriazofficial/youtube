<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Channel;

class MonetizationController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];

        $monetization = $channelModel->db->table('monetization')
            ->where('channel_id', $channelId)
            ->first();

        $subs = (int) $channel['subscriber_count'];
        $totalViews = (int) (new Channel())->db->table('videos')
            ->where('channel_id', $channelId)
            ->where('visibility', 'public')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->sum('view_count');

        $watchHours = (float) (new Channel())->db->table('video_views')
            ->join('videos', 'video_views.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->sum('watch_duration') / 3600;

        $eligible = $subs >= 1000 && $watchHours >= 4000;

        return $this->view('creator.monetization', [
            'title' => 'Monetization',
            'activeMenu' => 'monetization',
            'channel' => $channel,
            'monetization' => $monetization,
            'subscriberCount' => $subs,
            'watchHours' => round($watchHours, 1),
            'totalViews' => $totalViews,
            'eligible' => $eligible,
        ]);
    }

    public function earnings(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $channelModel->db->table('earnings')
            ->where('channel_id', $channelId)
            ->orderBy('earned_at', 'DESC')
            ->paginate(30, $page);

        $totalEarnings = (float) $channelModel->db->table('earnings')
            ->where('channel_id', $channelId)
            ->sum('amount');

        $thisMonth = (float) $channelModel->db->table('earnings')
            ->where('channel_id', $channelId)
            ->where('earned_at', '>=', date('Y-m-01'))
            ->sum('amount');

        $lastMonth = (float) $channelModel->db->table('earnings')
            ->where('channel_id', $channelId)
            ->where('earned_at', '>=', date('Y-m-01', strtotime('-1 month')))
            ->where('earned_at', '<', date('Y-m-01'))
            ->sum('amount');

        return $this->view('creator.earnings', [
            'title' => 'Earnings',
            'activeMenu' => 'monetization',
            'channel' => $channel,
            'earnings' => $result['data'],
            'pagination' => $result,
            'totalEarnings' => $totalEarnings,
            'thisMonth' => $thisMonth,
            'lastMonth' => $lastMonth,
        ]);
    }

    public function payouts(): Response
    {
        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            $this->session->flash('error', 'Channel not found.');
            return $this->redirect('/');
        }

        $channelId = (int) $channel['id'];
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $channelModel->db->table('payouts')
            ->where('channel_id', $channelId)
            ->orderBy('created_at', 'DESC')
            ->paginate(20, $page);

        $pendingPayout = (float) $channelModel->db->table('earnings')
            ->where('channel_id', $channelId)
            ->where('is_paid', 0)
            ->sum('amount');

        return $this->view('creator.payouts', [
            'title' => 'Payouts',
            'activeMenu' => 'monetization',
            'channel' => $channel,
            'payouts' => $result['data'],
            'pagination' => $result,
            'pendingPayout' => $pendingPayout,
        ]);
    }

    public function apply(): Response
    {
        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $userId = (int) $this->session->get('user_id');
        $channelModel = new Channel();
        $channel = $channelModel->findByUserId($userId);

        if ($channel === null) {
            return $this->respondWithError('Channel not found.');
        }

        $channelId = (int) $channel['id'];

        $existing = $channelModel->db->table('monetization')
            ->where('channel_id', $channelId)
            ->first();

        if ($existing !== null) {
            $this->session->flash('error', 'You have already applied for monetization.');
            return $this->redirect('/creator/monetization');
        }

        $channelModel->db->table('monetization')->insert([
            'channel_id' => $channelId,
            'status' => 'pending',
            'applied_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->flash('success', 'Monetization application submitted successfully.');
        return $this->redirect('/creator/monetization');
    }
}
