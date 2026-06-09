<header class="cf-topbar">
    <div class="cf-container cf-nav">
        <div class="cf-nav-left">
            <a href="{{ route('home') }}" class="cf-brand">
                <span class="cf-brand-mark" aria-hidden="true">
                    <img src="/assets/campus-found-logo-nav.png" alt="" class="cf-brand-logo">
                </span>
                <span>Campus Found</span>
            </a>
            <nav class="cf-nav-links" aria-label="Primary">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('board.index') }}" class="{{ request()->routeIs('board.index') ? 'active' : '' }}">Board</a>
                <a href="{{ route('claims.index') }}" class="{{ request()->routeIs('claims.*') ? 'active' : '' }}">Claims</a>
                <a href="{{ request()->routeIs('home') ? '#how-it-works' : route('home').'#how-it-works' }}">About</a>
            </nav>
        </div>
        <div class="cf-nav-actions">
            <button type="button" class="cf-theme-toggle" data-theme-toggle aria-label="Switch to dark mode" title="Toggle dark mode">
                <i class="bi bi-moon-stars" data-theme-icon></i>
            </button>
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
