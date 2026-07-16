<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class SupportTicket extends Model
{
    protected string $table = 'support_tickets';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'user_id',
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [];

    public function getUserTickets(int $userId, int $limit = 20, int $page = 1): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, $page);
    }

    public function getOpenTickets(int $limit = 50, int $page = 1): array
    {
        return $this->where('status', 'open')
            ->orderBy('created_at', 'ASC')
            ->paginate($limit, $page);
    }

    public function getByPriority(string $priority, int $limit = 50): array
    {
        return $this->where('priority', $priority)
            ->where('status', '!=', 'resolved')
            ->orderBy('created_at', 'ASC')
            ->limit($limit)
            ->get();
    }

    public function assignTicket(int $ticketId, int $assignedTo): bool
    {
        return $this->updateById($ticketId, [
            'assigned_to' => $assignedTo,
            'status'      => 'in_progress',
        ]);
    }

    public function resolveTicket(int $ticketId): bool
    {
        return $this->updateById($ticketId, [
            'status'      => 'resolved',
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function closeTicket(int $ticketId): bool
    {
        return $this->updateById($ticketId, [
            'status' => 'closed',
        ]);
    }

    public function addMessage(int $ticketId, int $userId, string $message, bool $isAdmin = false): bool
    {
        return (bool) $this->db->table('ticket_messages')->insert([
            'ticket_id'  => $ticketId,
            'user_id'    => $userId,
            'message'    => $message,
            'is_admin'   => $isAdmin,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getMessages(int $ticketId, int $limit = 50): array
    {
        return $this->db->table('ticket_messages')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'ASC')
            ->limit($limit)
            ->get();
    }
}
