<nav class="app-nav">
    <div class="app-nav-inner">
        <a href="{{ route('home') }}" class="app-nav-brand">EVENT<span>ORA</span></a>
        <ul class="app-nav-links">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Panel</a>
            </li>
            <li>
                <a href="{{ route('organizador.eventos.index') }}" class="{{ request()->routeIs('organizador.*') ? 'active' : '' }}">Mis eventos</a>
            </li>
            <li>
                <a href="{{ route('eventos.index') }}" target="_blank">Catálogo público</a>
            </li>
        </ul>
        <div class="d-flex align-items-center gap-2">
            <span class="app-nav-user">{{ auth()->user()->nombreCompleto() }}</span>
            <a href="{{ route('profile.edit') }}" class="btn-outline btn-sm">Perfil</a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline mb-0">
                @csrf
                <button type="submit" class="btn-gold btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>
