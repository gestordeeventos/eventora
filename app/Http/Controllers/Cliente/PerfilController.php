<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Services\UsuarioFotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function __construct(
        private UsuarioFotoService $fotoService
    ) {}

    public function show(): View
    {
        $user = auth()->user();
        abort_unless($user->isCliente(), 403);

        $pendientes = $user->reservas()
            ->where('estado', 'pendiente')
            ->with(['evento', 'reservaBoletos.boleto'])
            ->latest()
            ->take(5)
            ->get();

        return view('cliente.perfil.index', compact('user', 'pendientes'));
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isCliente(), 403);

        $request->validate([
            'avatar_data' => ['nullable', 'string'],
            'avatar_remove' => ['nullable', 'in:0,1'],
        ]);

        try {
            $rutaAnterior = $user->foto_perfil_url;

            if ($request->input('avatar_remove') === '1') {
                $this->fotoService->eliminarDeUsuario($user);
                $nuevaRuta = null;
            } elseif ($request->filled('avatar_data')) {
                $nuevaRuta = $this->fotoService->guardarDesdeBase64(
                    $request->input('avatar_data'),
                    $user->foto_perfil_url
                );
            } else {
                return back()->with('error', 'Selecciona una imagen o aplica el recorte antes de guardar.');
            }

            $user->update([
                'foto_perfil_url' => $nuevaRuta,
                'foto_perfil_updated_at' => $nuevaRuta !== $rutaAnterior ? now() : $user->foto_perfil_updated_at,
            ]);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'avatar_file' => $e->getMessage(),
            ]);
        }

        $mensaje = $request->input('avatar_remove') === '1'
            ? 'Foto de perfil eliminada.'
            : '¡Foto de perfil actualizada correctamente!';

        return redirect()->route('cliente.perfil')->with('success', $mensaje);
    }

    public function updateDatos(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isCliente(), 403);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'email' => [
                'required', 'email', 'max:120',
                Rule::unique('usuarios', 'email')->ignore($user->id_usuario, 'id_usuario'),
            ],
            'telefono' => ['nullable', 'string', 'digits:10'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $datos = [
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $datos['password_hash'] = Hash::make($validated['password']);
        }

        $user->update($datos);

        return redirect()->route('cliente.perfil')->with('success', 'Datos personales actualizados.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isCliente(), 403);

        $request->validate([
            'confirmar' => ['required', 'in:ELIMINAR'],
        ]);

        DB::transaction(function () use ($user) {
            foreach ($user->reservas()->with('reservaBoletos')->get() as $reserva) {
                if ($reserva->estado === 'pagada') {
                    foreach ($reserva->reservaBoletos as $linea) {
                        Boleto::where('id_boleto', $linea->id_boleto)
                            ->decrement('cantidad_vendida', $linea->cantidad);
                    }
                }
                $reserva->delete();
            }

            $this->fotoService->eliminarDeUsuario($user);
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Tu cuenta fue eliminada permanentemente.');
    }
}
