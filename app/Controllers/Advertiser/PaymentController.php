<?php

declare(strict_types=1);

namespace App\Controllers\Advertiser;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('advertiser', 'admin')) {
            return $this->redirect('/');
        }

        $userId = (int) $this->session->get('user_id');
        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));

        $result = $db->table('payouts')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('advertiser.payments', [
            'title' => 'Payment History',
            'activeMenu' => 'payments',
            'payments' => $result['data'],
            'pagination' => $result,
        ]);
    }
}
