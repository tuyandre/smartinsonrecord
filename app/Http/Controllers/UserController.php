<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $actor = $request->user();

        $users = User::with('role.permissions')->orderBy('name')->get();

        return view('pages.users.index', [
            'users' => $users,
            'roles' => $this->assignableRoles($actor),
            'manageable' => $users->filter(fn (User $u) => $actor->canGrant($u->role))->pluck('id')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rcvd_id' => ['required', 'string', 'max:255', 'unique:users,rcvd_id'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'role_id' => ['required', Rule::in($this->assignableRoles($request->user())->modelKeys())],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create($data);

        return $this->done('User created');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        if (! $actor->canGrant($user->role)) {
            return $this->denied('You are not allowed to change this user');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rcvd_id' => ['required', 'string', 'max:255', Rule::unique('users', 'rcvd_id')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'role_id' => ['required', Rule::in($this->assignableRoles($actor)->modelKeys())],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $roleChanged = (int) $data['role_id'] !== (int) $user->role_id;

        if ($roleChanged && $user->is($actor)) {
            return $this->denied('You cannot change your own role');
        }

        if ($roleChanged && $user->isSuperAdmin() && $this->lastSuperAdmin($user)) {
            return $this->denied('At least one Super Admin must remain');
        }

        $user->update($data);

        return $this->done('User updated');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        if ($user->is($actor)) {
            return $this->denied('You cannot delete your own account');
        }

        if (! $actor->canGrant($user->role)) {
            return $this->denied('You are not allowed to delete this user');
        }

        if ($user->isSuperAdmin() && $this->lastSuperAdmin($user)) {
            return $this->denied('At least one Super Admin must remain');
        }

        $user->delete();

        return $this->done('User deleted');
    }

    /** Roles the acting user may hand out: only those whose permissions they hold themselves. */
    private function assignableRoles(User $actor)
    {
        return Role::with('permissions')->orderBy('label')->get()
            ->filter(fn (Role $role) => $actor->canGrant($role))
            ->values();
    }

    private function lastSuperAdmin(User $user): bool
    {
        return User::where('role_id', $user->role_id)->whereKeyNot($user->id)->doesntExist();
    }

    private function done(string $text): RedirectResponse
    {
        return redirect()->route('users.index')->with('swal', ['title' => 'Success!', 'text' => $text, 'icon' => 'success']);
    }

    private function denied(string $text): RedirectResponse
    {
        return redirect()->route('users.index')->with('swal', ['title' => 'Error!', 'text' => $text, 'icon' => 'warning']);
    }
}
