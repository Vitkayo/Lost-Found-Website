<header class="cf-topbar">
    <div class="cf-container cf-nav">
        <div class="cf-nav-left">
            <a href="{{ route('home') }}" class="cf-brand">LOST <span>&amp; FOUND</span></a>
            <nav class="cf-nav-links" aria-label="Primary">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('board.index') }}" class="{{ request()->routeIs('board.index') ? 'active' : '' }}">Board</a>
                <a href="{{ route('claims.index') }}" class="{{ request()->routeIs('claims.*') ? 'active' : '' }}">Claims</a>
                <a href="{{ request()->routeIs('home') ? '#how-it-works' : route('home').'#how-it-works' }}">About</a>
            </nav>
        </div>
        <div class="cf-nav-actions">
            <a href="{{ route('report.create') }}" class="cf-btn cf-btn-primary cf-nav-report {{ request()->routeIs('report.*') ? 'active' : '' }}">+ Report</a>
            @if (session('is_admin'))
                <a href="{{ route('admin.dashboard') }}" class="cf-btn cf-btn-outline cf-nav-admin">Dashboard</a>
                <form method="post" action="{{ route('admin.logout') }}" class="cf-nav-logout">
                    @csrf
                    <button type="submit" class="cf-btn cf-btn-danger">Logout</button>
                </form>
            @endif
        </div>
    </div>
</header>
