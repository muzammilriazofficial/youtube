<?php

declare(strict_types=1);

namespace App\Controllers\Viewer;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Database;

class DownloadsController extends Controller
{
    public function index(): Response
    {
        $userId = (int) $this->session->get('user_id');

        $db = Database::getInstance();
        $downloads = $db->table('downloads')
            ->join('videos', 'downloads.video_id', '=', 'videos.id')
            ->where('downloads.user_id', $userId)
            ->orderBy('downloads.created_at', 'DESC')
            ->paginate(20, (int) $this->request->query('page', 1));

        return $this->view('viewer.downloads', [
            'title' => 'Downloads',
            'downloads' => $downloads,
        ]);
    }
}
