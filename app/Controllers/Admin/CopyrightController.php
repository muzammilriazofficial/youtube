<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class CopyrightController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = max(1, (int) $this->request->input('page', 1));
        $status = $this->request->input('status', '');

        $query = $db->table('copyright_claims')
            ->leftJoin('users', 'copyright_claims.claimant_id', '=', 'users.id')
            ->leftJoin('videos', 'copyright_claims.video_id', '=', 'videos.id');

        if ($status !== '') {
            $query = $query->where('copyright_claims.status', $status);
        }

        $claims = $query->select(
            'copyright_claims.*',
            'users.username as claimant_username',
            'videos.title as video_title'
        )
            ->orderBy('copyright_claims.created_at', 'DESC')
            ->paginate(20, $page);

        return $this->view('admin.copyright', [
            'title' => 'Copyright Claims',
            'activeMenu' => 'copyright',
            'claims' => $claims,
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
        $claimId = (int) $id;
        $notes = $this->request->input('notes', '');

        switch ($action) {
            case 'accept':
                $claim = $db->table('copyright_claims')->where('id', $claimId)->first();
                if ($claim) {
                    $db->table('videos')->where('id', (int) $claim['video_id'])->update([
                        'status' => 'removed',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $db->table('copyright_claims')->where('id', $claimId)->update([
                    'status' => 'accepted',
                    'admin_notes' => $notes,
                    'resolved_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Copyright claim accepted. Video removed.')->redirect('/admin/copyright');

            case 'reject':
                $db->table('copyright_claims')->where('id', $claimId)->update([
                    'status' => 'rejected',
                    'admin_notes' => $notes,
                    'resolved_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Copyright claim rejected.')->redirect('/admin/copyright');

            case 'counter_notify':
                $db->table('copyright_claims')->where('id', $claimId)->update([
                    'status' => 'counter_notified',
                    'admin_notes' => $notes,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return $this->withSuccess('Counter-notification sent.')->redirect('/admin/copyright');

            default:
                return $this->withError('Invalid action.')->redirect('/admin/copyright');
        }
    }
}
