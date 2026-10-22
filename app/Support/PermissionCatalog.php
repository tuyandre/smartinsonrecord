<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\Role;

/**
 * Single source of truth for the permissions the application knows about, which route needs which
 * permission, and the roles that ship by default. Roles themselves (and which permissions each one
 * holds) are data, managed from the Roles & Permissions page.
 */
class PermissionCatalog
{
    public const SUPERADMIN = 'superadmin';

    public const DEFAULT_ROLE = 'vet';

    /** group => [permission name => label] */
    public const PERMISSIONS = [
        'Dashboard' => [
            'dashboard.view' => 'View dashboard',
        ],
        'Cows' => [
            'cows.view' => 'View registered cows list',
            'cows.create' => 'Register cows',
            'cows.edit' => 'Edit cows',
            'cows.delete' => 'Delete cows',
        ],
        'Bulls' => [
            'bulls.view' => 'View registered bulls list',
            'bulls.create' => 'Register bulls',
            'bulls.edit' => 'Edit bulls',
            'bulls.delete' => 'Delete bulls',
        ],
        'Semen' => [
            'semen.view' => 'View semen list',
            'semen.create' => 'Register semen',
            'semen.edit' => 'Edit semen',
            'semen.delete' => 'Delete semen',
        ],
        'Inseminations' => [
            'inseminations.view' => 'View inseminations list',
            'inseminations.create' => 'Record inseminations',
        ],
        'Calves' => [
            'calves.view' => 'View calves list',
            'calves.create' => 'Record new born calves',
            'calves.edit' => 'Edit calf birth records',
            'calves.delete' => 'Delete calf birth records',
        ],
        'Pregnancy diagnosis' => [
            'pd.view' => 'View PD records',
            'pd.create' => 'Record PD results',
        ],
        'Analysis' => [
            'pedigree.view' => 'View pedigree',
            'reports.view' => 'View semen info and reports',
        ],
        'Veterinarians' => [
            'veterinarians.view' => 'View the veterinarians register',
            'veterinarians.create' => 'Register veterinarians',
            'veterinarians.edit' => 'Edit veterinarians',
            'veterinarians.delete' => 'Delete veterinarians',
            'veterinarians.deactivate' => 'Activate and deactivate veterinarians',
            'veterinarians.import' => 'Import the veterinarians list',
        ],
        'Users' => [
            'users.view' => 'View users',
            'users.create' => 'Create users',
            'users.edit' => 'Edit users and assign roles',
            'users.delete' => 'Delete users',
        ],
        'Roles' => [
            'roles.manage' => 'Manage roles and permissions',
        ],
    ];

    /** route name => permission required to reach it (unlisted routes only need a login) */
    public const ROUTES = [
        'dashboard' => 'dashboard.view',
        'registration.index' => 'cows.create',
        'registration.store' => 'cows.create',
        'registration.check' => 'cows.create',
        'registration.details' => 'cows.create',
        'registration.update' => 'cows.edit',
        'registration.destroy' => 'cows.delete',
        'cows.show' => 'cows.view',
        'cows.edit' => 'cows.edit',
        'bulls.index' => 'bulls.view',
        'bulls.show' => 'bulls.view',
        'bulls.create' => 'bulls.create',
        'bulls.store' => 'bulls.create',
        'bulls.edit' => 'bulls.edit',
        'bulls.update' => 'bulls.edit',
        'bulls.destroy' => 'bulls.delete',
        'semen.index' => 'semen.view',
        'semen.show' => 'semen.view',
        'semen.create' => 'semen.create',
        'semen.store' => 'semen.create',
        'semen.edit' => 'semen.edit',
        'semen.update' => 'semen.edit',
        'semen.destroy' => 'semen.delete',
        'insemination.index' => 'inseminations.create',
        'insemination.store' => 'inseminations.create',
        'insemination.last-mating' => 'inseminations.create',
        'insemination.cows' => 'inseminations.create',
        'insemination.relation' => 'inseminations.create',
        'newborn.index' => 'calves.create',
        'newborn.store' => 'calves.create',
        'newborn.data' => 'calves.create',
        'newborn.insemination' => 'calves.create',
        'newborn.mothers' => 'calves.create',
        'calves.show' => 'calves.view',
        'calves.edit' => 'calves.edit',
        'calves.update' => 'calves.edit',
        'calves.destroy' => 'calves.delete',
        'pd.index' => 'pd.create',
        'pd.store' => 'pd.create',
        'pd.lookup' => 'pd.create',
        'pedigree.index' => 'pedigree.view',
        'pedigree.search' => 'pedigree.view',
        'print.insemination' => 'inseminations.view',
        'print.calf' => 'calves.view',
        'reporting.index' => 'reports.view',
        'reporting.animals' => 'reports.view',
        'records.cows' => 'cows.view',
        'records.inseminations' => 'inseminations.view',
        'records.calves' => 'calves.view',
        'records.pd' => 'pd.view',
        'veterinarians.index' => 'veterinarians.view',
        'veterinarians.show' => 'veterinarians.view',
        'veterinarians.create' => 'veterinarians.create',
        'veterinarians.store' => 'veterinarians.create',
        'veterinarians.edit' => 'veterinarians.edit',
        'veterinarians.update' => 'veterinarians.edit',
        'veterinarians.destroy' => 'veterinarians.delete',
        'veterinarians.toggle' => 'veterinarians.deactivate',
        'veterinarians.import' => 'veterinarians.import',
        'veterinarians.import.store' => 'veterinarians.import',
        'veterinarians.template' => 'veterinarians.import',
        'users.index' => 'users.view',
        'users.store' => 'users.create',
        'users.update' => 'users.edit',
        'users.destroy' => 'users.delete',
        'roles.index' => 'roles.manage',
        'roles.store' => 'roles.manage',
        'roles.update' => 'roles.manage',
        'roles.destroy' => 'roles.manage',
    ];

    /** First page a user is allowed to open, used after login. */
    public static function homeRoute(\App\Models\User $user): string
    {
        foreach (['dashboard', 'records.cows', 'registration.index', 'bulls.index', 'bulls.create', 'insemination.index', 'newborn.index', 'pd.index', 'pedigree.index', 'reporting.index', 'users.index', 'roles.index'] as $route) {
            if ($user->hasPermission(self::ROUTES[$route])) {
                return $route;
            }
        }

        return 'dashboard';
    }

    public static function allNames(): array
    {
        return array_merge(...array_map('array_keys', array_values(self::PERMISSIONS)));
    }

    /** What a veterinarian may do: every record feature, but not user, role or veterinarian-register management. */
    public static function veterinarianPermissions(): array
    {
        return array_values(array_filter(
            self::allNames(),
            fn ($p) => ! str_starts_with($p, 'users.') && ! str_starts_with($p, 'veterinarians.') && $p !== 'roles.manage'
        ));
    }

    /** Roles created on first run: name => [label, description, is_system, permissions (null = all)] */
    private static function defaultRoles(): array
    {
        $all = self::allNames();

        return [
            self::SUPERADMIN => ['Super Admin', 'Full access to everything, including roles and permissions.', true, $all],
            'admin' => ['Admin', 'Manages users and all records.', false, array_values(array_diff($all, ['roles.manage']))],
            self::DEFAULT_ROLE => ['Veterinarian', 'Records and reviews insemination data.', true, self::veterinarianPermissions()],
        ];
    }

    /** Idempotent: adds missing permissions and default roles, and keeps Super Admin on every permission. */
    public static function sync(): void
    {
        $ids = [];
        foreach (self::PERMISSIONS as $group => $items) {
            foreach ($items as $name => $label) {
                $ids[$name] = Permission::updateOrCreate(['name' => $name], ['label' => $label, 'group' => $group])->id;
            }
        }

        foreach (self::defaultRoles() as $name => [$label, $description, $system, $permissions]) {
            $role = Role::firstOrCreate(['name' => $name], ['label' => $label, 'description' => $description, 'is_system' => $system]);

            if ($role->wasRecentlyCreated || $name === self::SUPERADMIN) {
                $role->permissions()->sync(array_map(fn ($p) => $ids[$p], $permissions));
            }
        }
    }
}
