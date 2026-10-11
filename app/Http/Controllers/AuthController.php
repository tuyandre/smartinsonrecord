<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required'],
            'password' => ['required'],
        ], [
            'login.required' => 'Username, email or phone and Password could not be empty!',
            'password.required' => 'Username, email or phone and Password could not be empty!',
        ]);

        $login = trim($credentials['login']);

        // Email addresses can be shared by several vets, so every match is tried.
        $candidates = User::query()
            ->where(fn ($q) => $q->where('username', $login)
                ->orWhere('email', $login)
                ->orWhere('phone', $login)
                ->orWhere('rcvd_id', $login))
            ->get();

        if ($candidates->isEmpty()) {
            return back()->with('swal', ['title' => 'Error Occurred!', 'text' => 'User not found!', 'icon' => 'warning'])->withInput($request->only('login'));
        }

        $user = $candidates->first(fn (User $candidate) => Hash::check($credentials['password'], $candidate->password));
        if (! $user) {
            return back()->with('swal', ['title' => 'Error Occurred!', 'text' => 'Incorrect username, email, phone or Password!', 'icon' => 'warning'])->withInput($request->only('login'));
        }

        Auth::login($user, $request->boolean('remember_me'));
        $request->session()->regenerate();

        return redirect()->intended(route(\App\Support\PermissionCatalog::homeRoute($user)));
    }

    public function showSignUp()
    {
        return view('auth.sign_up');
    }

    /** Looks a veterinarian up by National ID in the national vets registry. */
    public function lookupVet(Request $request): JsonResponse
    {
        $nid = (string) $request->input('national_id');

        try {
            $rows = Http::timeout(8)->get(config('services.vets.url'))->json() ?? [];
        } catch (\Throwable) {
            $rows = [];
        }

        $match = array_values(array_filter($rows, fn ($row) => ($row['National_id'] ?? null) === $nid));

        return response()->json($match);
    }

    public function signUp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rcvd' => ['required'],
            'names' => ['required'],
            'email' => ['required', 'email'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'pwd' => ['required', Password::min(8)],
            'repwd' => ['required', 'same:pwd'],
        ], [
            'rcvd.required' => 'RCVD ID, Name, and Email must not be empty!',
            'names.required' => 'RCVD ID, Name, and Email must not be empty!',
            'email.required' => 'RCVD ID, Name, and Email must not be empty!',
            'repwd.same' => 'Provided Passwords do not match!',
        ]);

        if (User::where('rcvd_id', $data['rcvd'])->exists()) {
            return back()->withInput($request->except('pwd', 'repwd'))
                ->with('swal', ['title' => 'Error Occurred!', 'text' => 'User already exists with the same RCVD ID!', 'icon' => 'warning']);
        }

        $user = User::create([
            'rcvd_id' => $data['rcvd'],
            'name' => $data['names'],
            'email' => $data['email'],
            'username' => $data['username'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password' => $data['pwd'],
            'role_id' => \App\Models\Role::where('name', \App\Support\PermissionCatalog::DEFAULT_ROLE)->value('id'),
        ]);

        try {
            Mail::raw(
                "Hello Veterinary {$user->name}, your account on the Smart Insemrecord system has been successfully created. You can now log in at ".route('login'),
                fn ($m) => $m->to($user->email, $user->name)->subject('Account Creation Verification')
            );
        } catch (\Throwable $e) {
            report($e); // account creation must not fail because mail is unavailable
        }

        return redirect()->route('login')->with('swal', ['title' => 'Good job!', 'text' => 'Account Successfully Created', 'icon' => 'success']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
