<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UsuarioFotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(
        private UsuarioFotoService $fotoService
    ) {}

    public function index(): View
    {
        $usuarios = User::whereIn('rol', ['admin', 'cliente'])
            ->orderBy('rol')
            ->orderBy('nombre')
            ->paginate(15);

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('admin.usuarios.form', [
            'usuario' => new User(['rol' => 'cliente', 'activo' => true]),
            'modo' => 'crear',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validar($request);
        $foto = $this->resolverFoto($request);

        User::create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'rol' => $validated['rol'],
            'activo' => $request->boolean('activo', true),
            'password_hash' => Hash::make($validated['password']),
            'foto_perfil_url' => $foto['url'],
            'foto_perfil_updated_at' => $foto['updated_at'],
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View|RedirectResponse
    {
        abort_if($usuario->rol === 'organizador', 404);

        if ($usuario->isProtectedAdmin()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'La cuenta admin@gestoreventos.com está protegida y no puede editarse desde aquí.');
        }

        return view('admin.usuarios.form', [
            'usuario' => $usuario,
            'modo' => 'editar',
        ]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        abort_if($usuario->rol === 'organizador', 404);

        if ($usuario->isProtectedAdmin()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'La cuenta admin@gestoreventos.com está protegida y no puede editarse.');
        }

        $validated = $this->validar($request, $usuario);
        $foto = $this->resolverFoto($request, $usuario);

        $datos = [
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'rol' => $validated['rol'],
            'activo' => $request->boolean('activo'),
            'foto_perfil_url' => $foto['url'],
            'foto_perfil_updated_at' => $foto['updated_at'],
        ];

        if (! empty($validated['password'])) {
            $datos['password_hash'] = Hash::make($validated['password']);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        abort_if($usuario->rol === 'organizador', 404);

        if ($usuario->isProtectedAdmin()) {
            return back()->with('error', 'No se puede eliminar la cuenta de administrador principal.');
        }

        if ($usuario->id_usuario === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia sesión activa.');
        }

        DB::transaction(function () use ($usuario) {
            foreach ($usuario->reservas()->with('reservaBoletos')->get() as $reserva) {
                if ($reserva->estado === 'pagada') {
                    foreach ($reserva->reservaBoletos as $linea) {
                        \App\Models\Boleto::where('id_boleto', $linea->id_boleto)
                            ->decrement('cantidad_vendida', $linea->cantidad);
                    }
                }
                $reserva->delete();
            }

            $this->fotoService->eliminarDeUsuario($usuario);
            $usuario->delete();
        });

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado permanentemente.');
    }

    /** @return array{url: ?string, updated_at: ?\Illuminate\Support\Carbon} */
    private function resolverFoto(Request $request, ?User $usuario = null): array
    {
        $request->validate([
            'avatar_data' => ['nullable', 'string'],
            'avatar_remove' => ['nullable', 'in:0,1'],
        ]);

        $rutaAnterior = $usuario?->foto_perfil_url;

        try {
            if ($request->input('avatar_remove') === '1') {
                $this->fotoService->eliminar($rutaAnterior);

                return ['url' => null, 'updated_at' => null];
            }

            if ($request->filled('avatar_data')) {
                $nueva = $this->fotoService->guardarDesdeBase64(
                    $request->input('avatar_data'),
                    $rutaAnterior
                );

                return ['url' => $nueva, 'updated_at' => now()];
            }
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'avatar_data' => $e->getMessage(),
            ]);
        }

        return [
            'url' => $rutaAnterior,
            'updated_at' => $usuario?->foto_perfil_updated_at,
        ];
    }

    private function validar(Request $request, ?User $usuario = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'apellido' => ['required', 'string', 'max:80'],
            'email' => [
                'required', 'email', 'max:120',
                Rule::unique('usuarios', 'email')->ignore($usuario?->id_usuario, 'id_usuario'),
            ],
            'telefono' => ['nullable', 'string', 'digits:10'],
            'rol' => ['required', Rule::in(['admin', 'cliente'])],
            'activo' => ['nullable', 'boolean'],
            'password' => [
                $usuario ? 'nullable' : 'required',
                'confirmed',
                Password::min(8),
            ],
        ]);
    }
}
