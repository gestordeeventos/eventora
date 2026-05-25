<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('cliente.auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'   => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:120', 'unique:usuarios,email'],
            'telefono' => ['nullable', 'string', 'digits:10'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
        ]);

        $user = User::create([
            'nombre'        => $request->nombre,
            'apellido'      => $request->apellido,
            'email'         => $request->email,
            'telefono'      => $request->telefono,
            'password_hash' => Hash::make($request->password),
            'rol'           => 'cliente',
            'activo'        => true,
        ]);

        event(new Registered($user));

        // Se eliminó la línea de Auth::login($user) para evitar el login automático.

        // Redirecciona al login con un mensaje de éxito que capturará la vista
        return redirect()->route('login')->with('success', '¡Cuenta creada con éxito! Inicia sesión para continuar.');
    }
}
