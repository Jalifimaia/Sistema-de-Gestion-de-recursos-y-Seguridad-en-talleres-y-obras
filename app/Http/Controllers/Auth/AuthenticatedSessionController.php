<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        // Registrar último acceso
        Auth::user()->update(['ultimo_acceso' => now()]);

        return redirect()->intended('/dashboard');
    }

    // Si falla, conservar el email y devolver error específico de contraseña
    return back()
        ->withInput($request->only('email'))
        ->withErrors(['password' => 'La contraseña es incorrecta.']);
}


    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');

    }
}
