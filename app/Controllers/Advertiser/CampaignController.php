<?php

declare(strict_types=1);

namespace App\Controllers\Advertiser;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class CampaignController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->redirect('/');
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $status = $this->request->input('status', '');

        $query = $db->table('ad_campaigns')
            ->where('advertiser_id', $userId);

        if ($status !== '') {
            $query = $query->where('status', $status);
        }

        $result = $query->orderBy('created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('advertiser.campaigns', [
            'title' => 'My Campaigns',
            'activeMenu' => 'campaigns',
            'campaigns' => $result['data'],
            'pagination' => $result,
            'status' => $status,
        ]);
    }

    public function create(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->redirect('/');
        }

        return $this->view('advertiser.campaign-form', [
            'title' => 'Create Campaign',
            'activeMenu' => 'campaigns',
            'campaign' => null,
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
            'name' => 'required|max:255',
            'budget' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $db->table('ad_campaigns')->insert([
            'advertiser_id' => $userId,
            'name' => $this->request->input('name'),
            'budget' => (float) $this->request->input('budget'),
            'spent' => 0,
            'start_date' => $this->request->input('start_date'),
            'end_date' => $this->request->input('end_date'),
            'status' => 'draft',
            'targeting' => $this->request->input('targeting') ? json_encode($this->request->input('targeting')) : null,
        ]);

        $this->withSuccess('Campaign created successfully.');
        return $this->redirect('/advertiser/campaigns');
    }

    public function edit(string $id): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->redirect('/');
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();
        $campaign = $db->table('ad_campaigns')
            ->where('id', (int) $id)
            ->where('advertiser_id', $userId)
            ->first();

        if ($campaign === null) {
            $this->withError('Campaign not found.');
            return $this->redirect('/advertiser/campaigns');
        }

        return $this->view('advertiser.campaign-form', [
            'title' => 'Edit Campaign',
            'activeMenu' => 'campaigns',
            'campaign' => $campaign,
        ]);
    }

    public function update(string $id): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();
        $campaign = $db->table('ad_campaigns')
            ->where('id', (int) $id)
            ->where('advertiser_id', $userId)
            ->first();

        if ($campaign === null) {
            return $this->respondWithError('Campaign not found.', 404);
        }

        $errors = $this->validate([
            'name' => 'required|max:255',
            'budget' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'in:draft,active,paused,completed,cancelled',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Validation failed: ' . implode(', ', array_merge(...array_values($errors))));
        }

        $db->table('ad_campaigns')->where('id', (int) $id)->update([
            'name' => $this->request->input('name'),
            'budget' => (float) $this->request->input('budget'),
            'start_date' => $this->request->input('start_date'),
            'end_date' => $this->request->input('end_date'),
            'status' => $this->request->input('status', $campaign['status']),
            'targeting' => $this->request->input('targeting') ? json_encode($this->request->input('targeting')) : $campaign['targeting'],
        ]);

        $this->withSuccess('Campaign updated successfully.');
        return $this->redirect('/advertiser/campaigns/' . $id . '/edit');
    }

    public function delete(string $id): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();
        $campaign = $db->table('ad_campaigns')
            ->where('id', (int) $id)
            ->where('advertiser_id', $userId)
            ->first();

        if ($campaign === null) {
            return $this->respondWithError('Campaign not found.', 404);
        }

        $db->table('ad_campaigns')->where('id', (int) $id)->update([
            'status' => 'cancelled',
        ]);

        if ($this->isApiRequest()) {
            return $this->json(['success' => true, 'message' => 'Campaign cancelled.']);
        }

        $this->withSuccess('Campaign cancelled.');
        return $this->redirect('/advertiser/campaigns');
    }
}
