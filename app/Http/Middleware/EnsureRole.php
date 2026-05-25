<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user && in_array($user->rol, $roles, true)) {
            return $next($request);
        }

        if ($user?->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No tienes acceso a esa sección.');
        }

        if ($user?->isCliente()) {
            return redirect()->route('cliente.eventos.index')
                ->with('error', 'No tienes acceso a esa sección.');
        }

        if ($user) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes acceso a esa sección.');
        }

        return redirect()->route('login');
    }
}
