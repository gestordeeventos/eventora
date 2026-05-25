<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="campo">
        <label for="current_password">Contraseña actual</label>
        <input type="password" id="current_password" name="current_password" required>
        @error('current_password', 'updatePassword')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="campo">
        <label for="password">Nueva contraseña</label>
        <input type="password" id="password" name="password" required>
        @error('password', 'updatePassword')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="campo">
        <label for="password_confirmation">Confirmar</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
    </div>
    <button type="submit" class="btn-gold">Actualizar contraseña</button>
    @if (session('status') === 'password-updated')
        <span class="text-success ms-2 small">Actualizada.</span>
    @endif
</form>
