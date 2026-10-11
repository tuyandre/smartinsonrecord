<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['rcvd_id', 'name', 'username', 'email', 'phone', 'password', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @var array<int, string>|null */
    private ?array $permissionCache = null;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->role?->isSuperAdmin();
    }

    public function roleLabel(): string
    {
        return $this->role?->label ?? 'No role';
    }

    /** @return array<int, string> */
    public function permissionNames(): array
    {
        if ($this->permissionCache === null) {
            $this->loadMissing('role.permissions');
            $this->permissionCache = $this->role?->permissionNames() ?? [];
        }

        return $this->permissionCache;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->isSuperAdmin() || in_array($permission, $this->permissionNames(), true);
    }

    /** A user may hand out a role (or manage someone holding it) only if they hold every permission it carries. */
    public function canGrant(?Role $role): bool
    {
        if ($role === null || $this->isSuperAdmin()) {
            return true;
        }

        if ($role->isSuperAdmin()) {
            return false;
        }

        $role->loadMissing('permissions');

        return array_diff($role->permissionNames(), $this->permissionNames()) === [];
    }
}
