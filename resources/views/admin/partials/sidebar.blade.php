<aside class="admin-sidebar">
    <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand font-display">Eventora</a>
    <p class="admin-sidebar-role">Panel administrador</p>
    <nav class="admin-sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="nav-icon">◈</span> Dashboard
        </a>
        <a href="{{ route('admin.eventos.index') }}" class="{{ request()->routeIs('admin.eventos.*') ? 'active' : '' }}">
            <span class="nav-icon">◉</span> Gestión de eventos
        </a>
        <a href="{{ route('admin.usuarios.index') }}" class="{{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
            <span class="nav-icon">◎</span> Usuarios
        </a>
        <a href="{{ route('home') }}" target="_blank">
            <span class="nav-icon">↗</span> Ver sitio público
        </a>
    </nav>
    <div class="admin-sidebar-footer">
        <p class="admin-user-name">{{ auth()->user()->nombreCompleto() }}</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-admin-outline-sm">Cerrar sesión</button>
        </form>
    </div>
</aside>
