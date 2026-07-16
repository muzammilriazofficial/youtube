<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    protected bool $timestamps = true;

    protected bool $softDeletes = true;

    protected array $fillable = [
        'username',
        'slug',
        'email',
        'password',
        'first_name',
        'last_name',
        'avatar',
        'banner',
        'bio',
        'phone',
        'role_id',
        'is_verified',
        'is_active',
        'is_banned',
    ];

    protected array $hidden = [
        'password',
    ];

    protected array $casts = [
        'is_verified' => 'boolean',
        'is_admin'    => 'boolean',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->db->table($this->table)
            ->where('email', $email)
            ->first();
    }

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    public function create(array $data): array
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        return parent::create($data);
    }

    public function updatePassword(int $id, string $password): bool
    {
        return $this->updateById($id, [
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);
    }

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password'] ?? '');
    }
}
