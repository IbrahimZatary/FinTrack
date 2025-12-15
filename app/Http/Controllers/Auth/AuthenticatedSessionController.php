<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
   public function store(LoginRequest $request)
{
    // TEMPORARY: Accept any email/password for testing
    $email = $request->email;
    $password = $request->password;
    
    // Check if user exists, if not create one
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        // Auto-create user with any email
        $user = User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => bcrypt($password),
            'email_verified_at' => now(),
        ]);
        
        // Log the user in
        Auth::login($user);
        
        $request->session()->regenerate();
        
        return redirect()->intended(RouteServiceProvider::HOME);
    }
    
    // If user exists, try to login normally
    $credentials = $request->only('email', 'password');
    
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(RouteServiceProvider::HOME);
    }
    
    // If password doesn't match, still login (temporary for testing)
    Auth::login($user);
    $request->session()->regenerate();
    
    return redirect()->intended(RouteServiceProvider::HOME);
}

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
