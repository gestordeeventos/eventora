<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UsuarioFotoService;
use App\Support\SslCertificate;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SocialAuthController extends Controller
{
    public function __construct(
        private UsuarioFotoService $fotoService
    ) {}

    public function redirect(string $provider): RedirectResponse
    {
        $this->validarProveedor($provider);
        if ($redirect = $this->asegurarConfigurado($provider)) {
            return $redirect;
        }

        return $this->socialite($provider)->redirect();
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $this->validarProveedor($provider);
        if ($redirect = $this->asegurarConfigurado($provider)) {
            return $redirect;
        }

        if ($request->filled('error')) {
            return redirect()->route('login')
                ->with('error', 'Inicio con '.ucfirst($provider).' cancelado. Puedes intentarlo de nuevo cuando quieras.');
        }

        try {
            $socialUser = $this->socialite($provider)->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')
                ->with('error', 'La sesión de '.ucfirst($provider).' expiró o fue cancelada. Vuelve a intentarlo.');
        } catch (GuzzleException) {
            return redirect()->route('login')
                ->with('error', 'No se pudo conectar con '.ucfirst($provider).'. Verifica tu conexión SSL e intenta de nuevo.');
        }

        $columna = $provider === 'google' ? 'google_id' : 'facebook_id';

        $user = User::where($columna, $socialUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $socialUser->getEmail())->first();
        }

        if ($user) {
            $datos = [
                $columna => $socialUser->getId(),
                'email_verificado_at' => $user->email_verificado_at ?? now(),
            ];
            $datos = array_merge($datos, $this->datosFotoPerfil($user, $socialUser));
            $user->update($datos);
        } else {
            $nombre = $socialUser->getName() ?? 'Usuario';
            $partes = explode(' ', $nombre, 2);

            $foto = $this->fotoService->sincronizarDesdeOAuth($socialUser->getAvatar());

            $user = User::create([
                'nombre' => $partes[0],
                'apellido' => $partes[1] ?? 'Eventora',
                'email' => $socialUser->getEmail(),
                $columna => $socialUser->getId(),
                'password_hash' => Hash::make(Str::random(32)),
                'rol' => 'cliente',
                'activo' => true,
                'email_verificado_at' => now(),
                'foto_perfil_url' => $foto,
                'foto_perfil_updated_at' => $foto ? now() : null,
            ]);
        }

        if (! $user->activo) {
            return redirect()->route('login')->with('error', 'Tu cuenta está desactivada.');
        }

        Auth::login($user, true);

        return $this->redirigirPorRol($user);
    }

    /** @return array<string, mixed> */
    private function datosFotoPerfil(User $user, SocialiteUser $socialUser): array
    {
        if ($this->fotoService->esFotoSubidaManual($user->foto_perfil_url)) {
            return [];
        }

        $nuevaRuta = $this->fotoService->sincronizarDesdeOAuth(
            $socialUser->getAvatar(),
            $user->foto_perfil_url
        );

        if ($nuevaRuta === $user->foto_perfil_url) {
            return [];
        }

        return [
            'foto_perfil_url' => $nuevaRuta,
            'foto_perfil_updated_at' => $nuevaRuta ? now() : null,
        ];
    }

    private function validarProveedor(string $provider): void
    {
        if (! in_array($provider, ['google', 'facebook'], true)) {
            throw new NotFoundHttpException();
        }
    }

    private function asegurarConfigurado(string $provider): ?RedirectResponse
    {
        $id = config("services.{$provider}.client_id");
        if (empty($id)) {
            return redirect()->route('login')
                ->with('error', 'Inicio con '.ucfirst($provider).' no está configurado. Agrega las credenciales en el archivo .env.');
        }

        return null;
    }

    private function redirigirPorRol(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isCliente()) {
            return redirect()->route('cliente.eventos.index');
        }

        return redirect()->route('dashboard');
    }

    private function socialite(string $provider)
    {
        $driver = Socialite::driver($provider);
        $options = SslCertificate::guzzleOptions();

        if ($options !== []) {
            $driver->setHttpClient(SslCertificate::httpClient());
        }

        return $driver;
    }
}
