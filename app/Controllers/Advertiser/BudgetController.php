<?php

declare(strict_types=1);

namespace App\Controllers\Advertiser;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class BudgetController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->redirect('/');
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $totalBudget = (float) $db->table('ad_campaigns')
            ->where('advertiser_id', $userId)
            ->sum('budget');

        $totalSpent = (float) $db->table('ad_campaigns')
            ->where('advertiser_id', $userId)
            ->sum('spent');

        $adSpend = (float) $db->table('advertisements')
            ->where('advertiser_id', $userId)
            ->sum('spend');

        $spendingHistory = $db->table('ad_campaigns')
            ->where('advertiser_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('advertiser.budget', [
            'title' => 'Budget Overview',
            'activeMenu' => 'budget',
            'totalBudget' => $totalBudget,
            'totalSpent' => $totalSpent,
            'adSpend' => $adSpend,
            'remaining' => $totalBudget - $totalSpent,
            'spendingHistory' => $spendingHistory['data'],
            'pagination' => $spendingHistory,
        ]);
    }

    public function add(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        if (!$this->validateCsrf()) {
            return $this->json(['error' => 'Invalid CSRF token'], 403);
        }

        $errors = $this->validate([
            'amount' => 'required|numeric',
        ]);

        if (!empty($errors)) {
            return $this->respondWithError('Please enter a valid amount.');
        }

        $amount = (float) $this->request->input('amount');
        if ($amount <= 0) {
            return $this->respondWithError('Amount must be greater than zero.');
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();

        $db->table('payouts')->insert([
            'user_id' => $userId,
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => 'balance',
        ]);

        $this->withSuccess('$' . number_format($amount, 2) . ' added to your budget.');
        return $this->redirect('/advertiser/budget');
    }
}
