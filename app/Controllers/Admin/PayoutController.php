<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class PayoutController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $status = $this->request->input('status', '');

        $query = $db->table('payouts')
            ->join('users', 'payouts.user_id', '=', 'users.id');

        if ($status !== '') {
            $query = $query->where('payouts.status', $status);
        }

        $payouts = $query->select('payouts.*', 'users.username')
            ->orderBy('payouts.created_at', 'DESC')
            ->paginate(20, $page);

        $totalPending = (float) $db->table('payouts')->where('status', 'pending')->sum('amount');
        $totalPaid = (float) $db->table('payouts')->where('status', 'completed')->sum('amount');

        return $this->view('admin.payouts', [
            'title' => 'Payout Management',
            'activeMenu' => 'payouts',
            'payouts' => $payouts,
            'status' => $status,
            'totalPending' => $totalPending,
            'totalPaid' => $totalPaid,
        ]);
    }

    public function process(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $payoutId = (int) $id;

        $payout = $db->table('payouts')->where('id', $payoutId)->first();
        if (!$payout) {
            return $this->withError('Payout not found.')->redirect('/admin/payouts');
        }

        $db->table('payouts')->where('id', $payoutId)->update([
            'status' => 'completed',
            'processed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->withSuccess('Payout marked as processed.')->redirect('/admin/payouts');
    }
}
