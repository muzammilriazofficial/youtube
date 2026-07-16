<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class CreatorController extends Controller
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

        $creators = $query->select(
            'channels.*',
            'users.username',
            'users.email',
            'users.avatar'
        )
            ->orderBy('channels.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.creators', [
            'title' => 'Creator Management',
            'activeMenu' => 'creators',
            'creators' => $creators,
            'search' => $search,
        ]);
    }
}
