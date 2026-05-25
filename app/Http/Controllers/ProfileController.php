<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        if ($request->user()->isCliente()) {
            return Redirect::route('cliente.perfil');
        }

        if ($request->user()->isAdmin()) {
            return Redirect::route('admin.dashboard');
        }

        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        if ($request->user()->isProtectedAdmin()) {
            return Redirect::route('admin.dashboard')
                ->with('error', 'La cuenta de administrador principal no puede modificarse.');
        }

        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verificado_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()->isProtectedAdmin()) {
            return Redirect::route('admin.dashboard')
                ->with('error', 'La cuenta de administrador principal no puede eliminarse.');
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
