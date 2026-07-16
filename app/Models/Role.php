<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Role extends Model
{
    protected string $table = 'roles';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [];

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function getPermissions(int $roleId): array
    {
        return $this->db->table('role_permissions')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('role_permissions.role_id', $roleId)
            ->get();
    }

    public function getUsers(int $roleId, int $limit = 100): array
    {
        return $this->db->table('user_roles')
            ->join('users', 'user_roles.user_id', '=', 'users.id')
            ->where('user_roles.role_id', $roleId)
            ->limit($limit)
            ->get();
    }

    public function givePermission(int $roleId, int $permissionId): bool
    {
        $existing = $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->first();

        if ($existing !== null) {
            return true;
        }

        return (bool) $this->db->table('role_permissions')->insert([
            'role_id'       => $roleId,
            'permission_id' => $permissionId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function revokePermission(int $roleId, int $permissionId): bool
    {
        return $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete() > 0;
    }

    public function userHasRole(int $userId, string $roleSlug): bool
    {
        return $this->db->table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $userId)
            ->where('roles.slug', $roleSlug)
            ->exists();
    }

    public function userHasAnyRole(int $userId, array $roleSlugs): bool
    {
        return $this->db->table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $userId)
            ->whereIn('roles.slug', $roleSlugs)
            ->exists();
    }

    public function assignRole(int $userId, int $roleId): bool
    {
        $existing = $this->db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->first();

        if ($existing !== null) {
            return true;
        }

        return (bool) $this->db->table('user_roles')->insert([
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function removeRole(int $userId, int $roleId): bool
    {
        return $this->db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete() > 0;
    }
}
