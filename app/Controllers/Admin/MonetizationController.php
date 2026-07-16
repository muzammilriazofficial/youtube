<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class MonetizationController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $status = $this->request->input('status', '');

        $query = $db->table('monetization_settings')
            ->join('channels', 'monetization_settings.channel_id', '=', 'channels.id')
            ->join('users', 'channels.user_id', '=', 'users.id');

        if ($status !== '') {
            $query = $query->where('monetization_settings.status', $status);
        }

        $monetizations = $query->select(
            'monetization_settings.*',
            'users.username',
            'channels.name as channel_name'
        )
            ->orderBy('monetization_settings.created_at', 'DESC')
            ->paginate(20, $page);

        $totalApproved = $db->table('monetization_settings')->where('status', 'approved')->count();
        $totalPending = $db->table('monetization_settings')->where('status', 'pending')->count();
        $totalRejected = $db->table('monetization_settings')->where('status', 'rejected')->count();

        return $this->view('admin.monetization', [
            'title' => 'Monetization Settings',
            'activeMenu' => 'monetization',
            'monetizations' => $monetizations,
            'status' => $status,
            'totalApproved' => $totalApproved,
            'totalPending' => $totalPending,
            'totalRejected' => $totalRejected,
        ]);
    }
}
