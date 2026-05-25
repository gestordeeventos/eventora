<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isOrganizador()) {
            return view('dashboard.organizador', [
                'misEventos' => $user->eventosOrganizados()->with('tipoEvento')->latest()->take(6)->get(),
                'totalEventos' => $user->eventosOrganizados()->count(),
            ]);
        }

        return redirect()->route('cliente.eventos.index');
    }
}
