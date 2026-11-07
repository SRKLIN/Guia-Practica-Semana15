<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistroRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function mostrarRegistro(): View
    {
        return view('auth.registro');
    }

    /**
     * Crea el usuario y lo deja con sesión iniciada.
     *
     * La contraseña se cifra sola cambia el ID de sesión al entrar, para que un ID que
     * alguien haya visto antes ya no sirva
     */
    public function registrar(RegistroRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('comercios.index')
            ->with('mensaje', "¡Bienvenido, {$user->name}!");
    }

    public function mostrarLogin(): View
    {
        return view('auth.login');
    }

    /**
     * attempt() busca el usuario por correo y compara el hash.
     *
     */
    public function login(Request $request): RedirectResponse
    {
        $datos = $request->only('email', 'password');

        if (Auth::attempt($datos, $request->boolean('recordar'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('comercios.index'));
        }

        return back()
            ->withErrors(['email' => 'Las credenciales no coinciden.'])
            ->onlyInput('email');
    }

    /**
     * Cerrar sesión borra los datos en el servidor, no solo la cookie:
     * invalidate() destruye la sesión y regenerateToken() renueva el CSRF
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
