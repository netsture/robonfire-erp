<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login authentication request.
     */
    public function login(Request $request)
    {
        $loginInput = trim($request->input('login', $request->input('email', '')));

        if (empty($loginInput)) {
            return back()->withErrors([
                'login' => 'The email or phone number field is required.',
            ])->onlyInput('login');
        }

        $request->validate([
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        $digitsOnly = preg_replace('/[^0-9]/', '', $loginInput);

        $user = User::where(function ($query) use ($loginInput, $digitsOnly) {
            $query->where('email', $loginInput)
                  ->orWhere('phone', $loginInput);

            if (!empty($digitsOnly)) {
                $query->orWhere('phone', $digitsOnly);
                if (strlen($digitsOnly) >= 10) {
                    $last10 = substr($digitsOnly, -10);
                    $query->orWhere('phone', $last10)
                          ->orWhere('phone', 'like', "%{$last10}");
                }
            }
        })->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if (!$user->isSuperAdmin()) {
                if ($user->status !== 'active') {
                    return back()->withErrors([
                        'login' => 'Your account is deactivated. Please contact administrator.',
                    ])->onlyInput('login');
                }

                if ($user->firm && $user->firm->status !== 'active') {
                    return back()->withErrors([
                        'login' => 'Your firm account is deactivated. Please contact administrator.',
                    ])->onlyInput('login');
                }
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    /**
     * Show registration form.
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'    => ['required', 'string', 'min:10', 'max:10'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $userCount = User::count();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => $userCount === 0 ? 'Admin' : 'Sales Executive',
            'status'   => 'active',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Account created successfully! Welcome to ERP System.');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out successfully.');
    }
}
