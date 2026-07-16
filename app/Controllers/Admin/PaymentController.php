<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');
        $status = $this->request->input('status', '');

        $query = $db->table('payments')
            ->join('users', 'payments.user_id', '=', 'users.id');

        if ($search !== '') {
            $query = $query->where('payments.transaction_id', 'LIKE', "%{$search}%")
                ->orWhere('users.username', 'LIKE', "%{$search}%");
        }

        if ($status !== '') {
            $query = $query->where('payments.status', $status);
        }

        $payments = $query->select('payments.*', 'users.username')
            ->orderBy('payments.created_at', 'DESC')
            ->paginate(20, $page);

        $totalRevenue = (float) $db->table('payments')->where('status', 'completed')->sum('amount');

        return $this->view('admin.payments', [
            'title' => 'Payment History',
            'activeMenu' => 'payments',
            'payments' => $payments,
            'search' => $search,
            'status' => $status,
            'totalRevenue' => $totalRevenue,
        ]);
    }
}
