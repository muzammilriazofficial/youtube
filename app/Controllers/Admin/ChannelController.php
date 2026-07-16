<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class ChannelController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $search = $this->request->input('search', '');

        $query = $db->table('channels')
            ->join('users', 'channels.user_id', '=', 'users.id');

        if ($search !== '') {
            $query = $query->where('channels.name', 'LIKE', "%{$search}%")
                ->orWhere('users.username', 'LIKE', "%{$search}%");
        }

        $channels = $query->select(
            'channels.*',
            'users.username',
            'users.email'
        )
            ->orderBy('channels.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.channels', [
            'title' => 'Channel Management',
            'activeMenu' => 'channels',
            'channels' => $channels,
            'search' => $search,
        ]);
    }

    public function action(string $id): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $action = $this->request->input('action', '');
        $channelId = (int) $id;

        switch ($action) {
            case 'verify':
                $db->table('channels')->where('id', $channelId)->update([
                    'is_verified' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Channel verified.')->redirect('/admin/channels');

            case 'unverify':
                $db->table('channels')->where('id', $channelId)->update([
                    'is_verified' => 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Channel verification removed.')->redirect('/admin/channels');

            case 'suspend':
                $db->table('channels')->where('id', $channelId)->update([
                    'status' => 'suspended',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Channel suspended.')->redirect('/admin/channels');

            case 'activate':
                $db->table('channels')->where('id', $channelId)->update([
                    'status' => 'active',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Channel activated.')->redirect('/admin/channels');

            default:
                return $this->withError('Invalid action.')->redirect('/admin/channels');
        }
    }
}
