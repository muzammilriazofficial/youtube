<?php

declare(strict_types=1);

namespace App\Controllers\Support;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;

class TicketController extends Controller
{
    public function index(): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();
        $page = (int) ($this->request->input('page', 1));
        $status = $this->request->input('status', '');
        $priority = $this->request->input('priority', '');
        $category = $this->request->input('category', '');
        $assigned = $this->request->input('assigned', '');
        $search = $this->request->input('search', '');

        $query = $db->table('support_tickets')
            ->join('users', 'support_tickets.user_id', '=', 'users.id')
            ->leftJoin('users as assignee', 'support_tickets.assigned_to', '=', 'assignee.id');

        if ($status !== '') {
            $query = $query->where('support_tickets.status', $status);
        }
        if ($priority !== '') {
            $query = $query->where('support_tickets.priority', $priority);
        }
        if ($category !== '') {
            $query = $query->where('support_tickets.category', $category);
        }
        if ($assigned === 'me') {
            $query = $query->where('support_tickets.assigned_to', (int) $this->session->get('user_id'));
        } elseif ($assigned === 'unassigned') {
            $query = $query->whereNull('support_tickets.assigned_to');
        }
        if ($search !== '') {
            $query = $query->where('support_tickets.subject', 'LIKE', "%{$search}%");
        }

        $tickets = $query->orderBy('support_tickets.created_at', 'DESC')->paginate(20, $page);

        return $this->view('support.tickets', [
            'title' => 'Support Tickets',
            'activeMenu' => 'tickets',
            'tickets' => $tickets,
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'category' => $category,
                'assigned' => $assigned,
                'search' => $search,
            ],
        ]);
    }

    public function show(int $id): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        $db = Database::getInstance();

        $ticket = $db->table('support_tickets')
            ->join('users', 'support_tickets.user_id', '=', 'users.id')
            ->leftJoin('users as assignee', 'support_tickets.assigned_to', '=', 'assignee.id')
            ->where('support_tickets.id', $id)
            ->first();

        if ($ticket === null) {
            return $this->redirect('/support/tickets');
        }

        $replies = $db->table('support_replies')
            ->join('users', 'support_replies.user_id', '=', 'users.id')
            ->where('support_replies.ticket_id', $id)
            ->orderBy('support_replies.created_at', 'ASC')
            ->get();

        $agents = $db->table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['support', 'admin'])
            ->select('users.id', 'users.username')
            ->get();

        $relatedVideo = null;
        if (!empty($ticket['video_id'])) {
            $relatedVideo = $db->table('videos')->find((int) $ticket['video_id']);
        }

        $relatedChannel = null;
        if (!empty($ticket['channel_id'])) {
            $relatedChannel = $db->table('channels')->find((int) $ticket['channel_id']);
        }

        return $this->view('support.ticket-show', [
            'title' => 'Ticket #' . $ticket['id'],
            'activeMenu' => 'tickets',
            'ticket' => $ticket,
            'replies' => $replies,
            'agents' => $agents,
            'relatedVideo' => $relatedVideo,
            'relatedChannel' => $relatedChannel,
        ]);
    }

    public function reply(int $id): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $message = trim((string) $this->request->input('message', ''));

        if ($message === '') {
            return $this->respondWithError('Reply message is required.');
        }

        $db = Database::getInstance();

        $ticket = $db->table('support_tickets')->find($id);
        if ($ticket === null) {
            return $this->redirect('/support/tickets');
        }

        $db->table('support_replies')->insert([
            'ticket_id' => $id,
            'user_id' => (int) $this->session->get('user_id'),
            'message' => $message,
            'is_agent' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $db->table('support_tickets')->where('id', $id)->update([
            'updated_at' => date('Y-m-d H:i:s'),
            'status' => $ticket['status'] === 'waiting_on_user' ? 'in_progress' : $ticket['status'],
        ]);

        $this->withSuccess('Reply sent successfully.');
        return $this->redirect("/support/tickets/{$id}");
    }

    public function assign(int $id): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $assignedTo = $this->request->input('assigned_to', '');
        $db = Database::getInstance();

        $update = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($assignedTo === 'self') {
            $update['assigned_to'] = (int) $this->session->get('user_id');
            $update['status'] = 'in_progress';
        } elseif ($assignedTo === '' || $assignedTo === 'unassign') {
            $update['assigned_to'] = null;
        } else {
            $update['assigned_to'] = (int) $assignedTo;
        }

        $db->table('support_tickets')->where('id', $id)->update($update);

        $this->withSuccess('Ticket assignment updated.');
        return $this->redirect("/support/tickets/{$id}");
    }

    public function updateStatus(int $id): Response
    {
        if (!$this->hasRole('support', 'admin')) {
            return $this->redirect('/');
        }

        if (!$this->validateCsrf()) {
            return $this->respondWithError('Invalid CSRF token.');
        }

        $status = (string) $this->request->input('status', '');
        $allowedStatuses = ['open', 'in_progress', 'waiting_on_user', 'resolved', 'closed'];

        if (!in_array($status, $allowedStatuses, true)) {
            return $this->respondWithError('Invalid status.');
        }

        $db = Database::getInstance();
        $db->table('support_tickets')->where('id', $id)->update([
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->withSuccess('Ticket status updated to ' . str_replace('_', ' ', $status) . '.');
        return $this->redirect("/support/tickets/{$id}");
    }
}
