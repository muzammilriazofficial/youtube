<?php

declare(strict_types=1);

namespace App\Controllers\Reviewer;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class MonetizationReviewController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $db->table('monetization_settings')
            ->join('channels', 'monetization_settings.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id')
            ->where('monetization_settings.is_enabled', 0)
            ->whereNotNull('monetization_settings.application_date')
            ->orderBy('monetization_settings.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('reviewer.monetization', [
            'title' => 'Monetization Applications',
            'activeMenu' => 'monetization',
            'applications' => $result['data'],
            'pagination' => $result,
        ]);
    }

    public function approve(string $id): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $db = Database::getInstance();
        $setting = $db->table('monetization_settings')->find((int) $id);

        if ($setting === null) {
            return $this->respondWithError('Monetization application not found.', 404);
        }

        $db->table('monetization_settings')->where('id', (int) $id)->update([
            'is_eligible' => 1,
            'is_enabled' => 1,
            'approval_date' => date('Y-m-d H:i:s'),
        ]);

        $db->table('channels')->where('id', $setting['channel_id'])->update([
            'is_partner' => 1,
        ]);

        $this->withSuccess('Monetization approved.');
        return $this->redirect('/reviewer/monetization');
    }

    public function reject(string $id): Response
    {
        if (!$this->hasRole('reviewer', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $errors = $this->validate([
            'reason' => 'required|max:1000',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Please provide a rejection reason.');
        }

        $db = Database::getInstance();
        $setting = $db->table('monetization_settings')->find((int) $id);

        if ($setting === null) {
            return $this->respondWithError('Monetization application not found.', 404);
        }

        $db->table('monetization_settings')->where('id', (int) $id)->update([
            'is_eligible' => 0,
        ]);

        $this->withSuccess('Monetization application rejected.');
        return $this->redirect('/reviewer/monetization');
    }
}
