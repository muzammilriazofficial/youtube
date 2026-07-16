<?php

declare(strict_types=1);

namespace App\Controllers\Creator;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Channel;

class CopyrightController extends Controller
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
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $channelModel->db->table('copyright_claims')
            ->join('videos', 'copyright_claims.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->orderBy('copyright_claims.created_at', 'DESC')
            ->paginate(20, $page);

        $totalClaims = $channelModel->db->table('copyright_claims')
            ->join('videos', 'copyright_claims.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->count();

        $activeClaims = $channelModel->db->table('copyright_claims')
            ->join('videos', 'copyright_claims.video_id', '=', 'videos.id')
            ->where('videos.channel_id', $channelId)
            ->where('copyright_claims.status', 'active')
            ->count();

        return $this->view('creator.copyright', [
            'title' => 'Copyright Claims',
            'activeMenu' => 'copyright',
            'channel' => $channel,
            'claims' => $result['data'],
            'pagination' => $result,
            'totalClaims' => $totalClaims,
            'activeClaims' => $activeClaims,
        ]);
    }
}
