@props(['usuario'])

@php
    $nombre = old('nombre', $usuario->nombre ?? 'U');
    $email = old('email', $usuario->email ?? 'eventora@local');
    $inicial = strtoupper(substr(trim($nombre), 0, 1)) ?: 'U';
    $color = $usuario->exists
        ? $usuario->colorAvatar()
        : (\App\Models\User::query()->make(['email' => $email])->colorAvatar());
    $fotoUrl = $usuario->exists ? $usuario->fotoPerfilUrl() : null;
    $tieneFoto = filled($fotoUrl);
@endphp

<div class="avatar-crop-block">
    <label class="avatar-crop-label">Foto de perfil</label>
    <p class="avatar-crop-hint mb-2">JPG o PNG · recorte cuadrado · opcional</p>

    <div class="perfil-user-row avatar-crop-preview-row">
        <div class="perfil-avatar-wrap">
            <div class="perfil-avatar-inner">
                <span id="perfil-avatar-initial" class="perfil-avatar-inicial"
                      style="@if(! $tieneFoto) background-color: {{ $color }}; @endif"
                      @if($tieneFoto) hidden @endif>{{ $inicial }}</span>
                <img id="perfil-avatar-display"
                     class="perfil-avatar-img"
                     alt="Vista previa de foto de perfil"
                     data-url="{{ $fotoUrl }}"
                     @if($tieneFoto) src="{{ $fotoUrl }}" @else hidden @endif>
            </div>
            <button type="button" class="perfil-avatar-edit-btn" id="avatar-cambiar-btn" title="Cambiar foto">+</button>
        </div>
    </div>

    <input type="file" id="avatar_file" accept="image/jpeg,image/jpg,image/png" hidden>
    <input type="hidden" name="avatar_data" id="avatar_data" value="">
    <input type="hidden" name="avatar_remove" id="avatar_remove" value="0">

    <div id="avatar-crop-wrap" class="avatar-crop-wrap" hidden>
        <p class="avatar-crop-hint">Arrastra para mover · zoom con rueda · recorte 1:1</p>
        <div class="avatar-crop-container">
            <img id="avatar-crop-image" alt="Recortar foto de perfil">
        </div>
        <div class="avatar-crop-actions">
            <button type="button" class="btn-admin-gold btn-sm" id="avatar-aplicar-recorte">Aplicar recorte</button>
            <button type="button" class="btn-admin-outline-sm" id="avatar-quitar">Quitar foto</button>
        </div>
    </div>
</div>
