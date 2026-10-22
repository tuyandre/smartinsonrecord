<?php

namespace App\Http\Controllers;

use App\Models\GlobalVeterinarian;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    /** Looks a veterinarian up by National ID in the national register (global_veterinarians). */
    public function lookupVet(Request $request): JsonResponse
    {
        $nid = preg_replace('/\s+/', '', (string) $request->input('national_id'));

        if (! preg_match('/^\d{16}$/', $nid)) {
            return response()->json(['found' => false, 'message' => 'The National ID must be exactly 16 digits.']);
        }

        $vet = GlobalVeterinarian::where('national_id', $nid)->first();

        if (! $vet) {
            return response()->json(['found' => false, 'message' => 'This National ID is not in the national veterinarians register, so you cannot sign up.']);
        }
        if (! $vet->is_active) {
            return response()->json(['found' => false, 'message' => 'This veterinarian is not active in the register. Contact the administrator.']);
        }
        if (User::where('rcvd_id', $vet->rcvd_id)->exists()) {
            return response()->json(['found' => false, 'message' => 'An account already exists for this veterinarian. Please log in.']);
        }

        return response()->json([
            'found' => true,
            'names' => $vet->names,
            'rcvd_id' => $vet->rcvd_id,
            'district' => $vet->district,
            'sector' => $vet->sector,
        ]);
    }

    public function signUp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nid' => ['required', 'regex:/^\d{16}$/'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'pwd' => ['required', Password::min(8)],
            'repwd' => ['required', 'same:pwd'],
            'confirm' => ['accepted'],
        ], [
            'nid.required' => 'Enter your National ID.',
            'nid.regex' => 'The National ID must be exactly 16 digits.',
            'email.required' => 'Enter your email address.',
            'phone.required' => 'Enter your phone number.',
            'phone.unique' => 'This phone number is already used by another account.',
            'repwd.same' => 'Provided Passwords do not match!',
            'confirm.accepted' => 'Confirm that you are the veterinarian shown above.',
        ]);

        // The register is the only source of identity: name and RCVD ID are never taken from the form.
        $vet = GlobalVeterinarian::where('national_id', $data['nid'])->first();
        $reason = match (true) {
            ! $vet => 'This National ID is not in the national veterinarians register, so you cannot sign up.',
            ! $vet->is_active => 'This veterinarian is not active in the register. Contact the administrator.',
            User::where('rcvd_id', $vet->rcvd_id)->exists() => 'An account already exists for this veterinarian. Please log in.',
            default => null,
        };
        if ($reason) {
            return back()->withInput($request->except('pwd', 'repwd'))->with('swal', ['title' => 'Cannot sign up', 'text' => $reason, 'icon' => 'warning']);
        }

        $user = User::create([
            'rcvd_id' => $vet->rcvd_id,
            'name' => $vet->names,
            'email' => $data['email'],
            'username' => $data['username'] ?? null,
            'phone' => $data['phone'],
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
