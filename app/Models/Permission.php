<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Permission extends Model
{
    protected string $table = 'permissions';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'name',
        'slug',
        'description',
        'group_name',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [];

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function getRoles(int $permissionId): array
    {
        return $this->db->table('role_permissions')
            ->join('roles', 'role_permissions.role_id', '=', 'roles.id')
            ->where('role_permissions.permission_id', $permissionId)
            ->get();
    }

    public function getByGroup(string $group): array
    {
        return $this->where('group_name', $group)
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getGroups(): array
    {
        $results = $this->db->table('permissions')
            ->select('DISTINCT `group_name`')
            ->orderBy('`group_name`', 'ASC')
            ->get();

        return array_column($results, 'group_name');
    }

    public function userHasPermission(int $userId, string $permissionSlug): bool
    {
        return $this->db->table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->join('role_permissions', 'roles.id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $userId)
            ->where('permissions.slug', $permissionSlug)
            ->exists();
    }

    public function userHasAnyPermission(int $userId, array $permissionSlugs): bool
    {
        return $this->db->table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->join('role_permissions', 'roles.id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $userId)
            ->whereIn('permissions.slug', $permissionSlugs)
            ->exists();
    }

    public function getUserPermissions(int $userId): array
    {
        return $this->db->table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->join('role_permissions', 'roles.id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $userId)
            ->get();
    }
}
