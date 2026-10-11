<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();

        $roles = Role::with('permissions:id,name')->withCount('users')->orderBy('label')->get();

        return view('pages.roles.index', [
            'roles' => $roles,
            'permissions' => Permission::orderBy('id')->get()->groupBy('group'),
            // permission ids this actor may hand out (everything for a Super Admin)
            'grantable' => $actor->isSuperAdmin()
                ? Permission::pluck('id')->all()
                : Permission::whereIn('name', $actor->permissionNames())->pluck('id')->all(),
            'editable' => $roles->filter(fn (Role $r) => $this->mayEdit($actor, $r))->pluck('id')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $slug = Str::slug($data['label'], '_');

        if ($slug === '' || Role::where('name', $slug)->exists()) {
            return $this->denied('A role with that name already exists');
        }

        $this->guardPermissions($request->user(), $data['permissions'] ?? []);

        $role = Role::create(['name' => $slug, 'label' => $data['label'], 'description' => $data['description'] ?? null]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return $this->done('Role created');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $actor = $request->user();

        if ($role->isSuperAdmin()) {
            return $this->denied('The Super Admin role always has every permission and cannot be edited');
        }

        if (! $this->mayEdit($actor, $role)) {
            return $this->denied('You can only edit roles whose permissions you hold yourself');
        }

        $data = $this->validated($request);
        $this->guardPermissions($actor, $data['permissions'] ?? []);

        $role->update(['label' => $data['label'], 'description' => $data['description'] ?? null]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return $this->done('Role updated');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return $this->denied('System roles cannot be deleted');
        }

        if (! $this->mayEdit($request->user(), $role)) {
            return $this->denied('You can only delete roles whose permissions you hold yourself');
        }

        if ($count = $role->users()->count()) {
            return $this->denied("{$count} user(s) still have this role. Assign them another role first");
        }

        $role->delete();

        return $this->done('Role deleted');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);
    }

    private function mayEdit(User $actor, Role $role): bool
    {
        return ! $role->isSuperAdmin() && $actor->canGrant($role);
    }

    /** Nobody can hand out a permission they do not hold themselves. */
    private function guardPermissions(User $actor, array $ids): void
    {
        if ($actor->isSuperAdmin() || $ids === []) {
            return;
        }

        $allowed = Permission::whereIn('name', $actor->permissionNames())->pluck('id')->all();

        abort_if(array_diff(array_map('intval', $ids), $allowed) !== [], 403, 'You cannot grant permissions you do not have.');
    }

    private function done(string $text): RedirectResponse
    {
        return redirect()->route('roles.index')->with('swal', ['title' => 'Success!', 'text' => $text, 'icon' => 'success']);
    }

    private function denied(string $text): RedirectResponse
    {
        return redirect()->route('roles.index')->with('swal', ['title' => 'Error!', 'text' => $text, 'icon' => 'warning']);
    }
}
