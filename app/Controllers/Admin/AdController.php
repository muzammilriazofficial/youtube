<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class AdController extends Controller
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

        $query = $db->table('advertisements')
            ->leftJoin('users', 'advertisements.advertiser_id', '=', 'users.id');

        if ($search !== '') {
            $query = $query->where('advertisements.title', 'LIKE', "%{$search}%")
                ->orWhere('users.username', 'LIKE', "%{$search}%");
        }

        if ($status !== '') {
            $query = $query->where('advertisements.status', $status);
        }

        $ads = $query->select('advertisements.*', 'users.username')
            ->orderBy('advertisements.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.advertisements', [
            'title' => 'Advertisement Management',
            'activeMenu' => 'advertisements',
            'ads' => $ads,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function action(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $action = $this->request->input('action', '');
        $adId = (int) $id;

        switch ($action) {
            case 'approve':
                $db->table('advertisements')->where('id', $adId)->update([
                    'status' => 'active',
                    'approved_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Ad approved.')->redirect('/admin/advertisements');

            case 'reject':
                $db->table('advertisements')->where('id', $adId)->update([
                    'status' => 'rejected',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Ad rejected.')->redirect('/admin/advertisements');

            case 'pause':
                $db->table('advertisements')->where('id', $adId)->update([
                    'status' => 'paused',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Ad paused.')->redirect('/admin/advertisements');

            case 'delete':
                $db->table('advertisements')->where('id', $adId)->update([
                    'deleted_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Ad deleted.')->redirect('/admin/advertisements');

            default:
                return $this->withError('Invalid action.')->redirect('/admin/advertisements');
        }
    }
}
