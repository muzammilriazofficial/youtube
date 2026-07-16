<?php

declare(strict_types=1);

namespace App\Controllers\Advertiser;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class AdController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->redirect('/');
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('advertiser.ads', [
            'title' => 'My Ads',
            'activeMenu' => 'ads',
            'ads' => $result['data'],
            'pagination' => $result,
        ]);
    }

    public function upload(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->redirect('/');
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $campaigns = $db->table('ad_campaigns')
            ->where('advertiser_id', $userId)
            ->whereIn('status', ['draft', 'active'])
            ->orderBy('name', 'ASC')
            ->get();

        return $this->view('advertiser.ad-upload', [
            'title' => 'Upload Ad',
            'activeMenu' => 'ads',
            'campaigns' => $campaigns,
        ]);
    }

    public function store(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $errors = $this->validate([
            'title' => 'required|max:255',
            'type' => 'required|in:bumper,skippable,non_skippable,display,overlay',
            'target_url' => 'url',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $data = [
            'advertiser_id' => $userId,
            'title' => $this->request->input('title'),
            'description' => $this->request->input('description', ''),
            'type' => $this->request->input('type'),
            'target_url' => $this->request->input('target_url', ''),
            'status' => 'pending',
            'impressions' => 0,
            'clicks' => 0,
            'spend' => 0,
        ];

        $file = $this->request->file('ad_file');
        if ($file !== null && $file['error'] === UPLOAD_ERR_OK) {
            $allowed = ['video/mp4', 'video/webm', 'image/jpeg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (in_array($type, $allowed, true)) {
                $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin';
                $filename = 'ad_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $uploadDir . DIRECTORY_SEPARATOR . $filename);
                $data['file_path'] = '/uploads/ads/' . $filename;
                $data['file_size'] = $file['size'];
            }
        }

        $db->table('advertisements')->insert($data);

        $this->withSuccess('Ad created successfully. It will be reviewed shortly.');
        return $this->redirect('/advertiser/ads');
    }
}
