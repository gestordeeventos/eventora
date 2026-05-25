<header class="cliente-nav">
    <div class="cliente-container cliente-nav-inner">
        <a href="{{ route('home') }}" class="cliente-logo font-display">Eventora</a>
        <nav class="cliente-nav-links">
            @auth
                @if (auth()->user()->isCliente())
                    <a href="{{ route('cliente.eventos.index') }}"
                       class="{{ request()->routeIs('cliente.eventos.*', 'eventos.*', 'reservas.*') && ! request()->routeIs('cliente.perfil*') ? 'active' : '' }}">
                        Explorar eventos
                    </a>
                    <a href="{{ route('cliente.compras.index') }}"
                       class="{{ request()->routeIs('cliente.compras.*', 'cliente.pago.*', 'cliente.ticket') ? 'active' : '' }}">
                        Mis compras
                    </a>
                    <a href="{{ route('cliente.perfil') }}"
                       class="{{ request()->routeIs('cliente.perfil*') ? 'active' : '' }}">
                        Mi perfil
                    </a>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Panel admin</a>
                    <a href="{{ route('admin.eventos.index') }}">Gestionar eventos</a>
                @elseif (auth()->user()->isOrganizador())
                    <a href="{{ route('dashboard') }}">Mi panel</a>
                    <a href="{{ route('organizador.eventos.index') }}">Mis eventos</a>
                @endif
            @else
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a>
                <a href="{{ route('eventos.index') }}" class="{{ request()->routeIs('eventos.*') ? 'active' : '' }}">Eventos</a>
            @endauth
        </nav>
        <div class="cliente-nav-actions">
            @auth
                @if (auth()->user()->isCliente())
                    <a href="{{ route('cliente.perfil') }}" class="cliente-nav-avatar" title="Mi perfil">
                        @if (auth()->user()->tieneFotoPerfil())
                            <img src="{{ auth()->user()->fotoPerfilUrl() }}" alt="">
                        @else
                            <span class="cliente-nav-avatar-inicial" style="background-color: {{ auth()->user()->colorAvatar() }}">
                                {{ auth()->user()->inicialesAvatar() }}
                            </span>
                        @endif
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="d-inline mb-0">
                    @csrf
                    <button type="submit" class="btn-nav-outline">Cerrar sesión</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-nav-outline">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="btn-nav-gold">Registrarse</a>
            @endauth
        </div>
    </div>
</header>
