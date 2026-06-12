<header class="cf-topbar">
    <div class="cf-container cf-nav">
        <button type="button" class="cf-menu-toggle" data-menu-toggle aria-expanded="false"
                aria-controls="cf-mobile-menu" aria-label="Open navigation menu">
            <i class="bi bi-list" data-menu-icon></i>
        </button>
        <div class="cf-nav-left">
            <a href="{{ route('home') }}" class="cf-brand">
                <span class="cf-brand-mark" aria-hidden="true">
                    <img src="/assets/campus-found-logo-nav.png" alt="" class="cf-brand-logo">
                </span>
                <span>Campus Found</span>
            </a>
        </div>
        <div class="cf-nav-dropdown" id="cf-mobile-menu">
            <nav class="cf-nav-links" aria-label="Primary">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="bi bi-house-door" aria-hidden="true"></i><span>Home</span></a>
                <a href="{{ route('board.index') }}" class="{{ request()->routeIs('board.index') ? 'active' : '' }}"><i class="bi bi-grid" aria-hidden="true"></i><span>Board</span></a>
                <a href="{{ route('claims.index') }}" class="{{ request()->routeIs('claims.*') ? 'active' : '' }}"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Claims</span></a>
                <a href="{{ request()->routeIs('home') ? '#how-it-works' : route('home').'#how-it-works' }}"><i class="bi bi-info-circle" aria-hidden="true"></i><span>About</span></a>
            </nav>
            <div class="cf-nav-actions">
                @if (session('is_admin'))
                    <a href="{{ route('admin.dashboard') }}" class="cf-btn cf-btn-outline cf-nav-admin">Dashboard</a>
                    <form method="post" action="{{ route('admin.logout') }}" class="cf-nav-logout">
                        @csrf
                        <button type="submit" class="cf-btn cf-btn-danger">Logout</button>
                    </form>
                @else
                    <a href="{{ route('report.create') }}" class="cf-btn cf-nav-report {{ request()->routeIs('report.*') ? 'active' : '' }}">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i><span>Report</span>
                    </a>
                    <details class="cf-account-menu">
                        <summary aria-label="Open account menu" title="Account">
                            <i class="bi bi-person-fill" aria-hidden="true"></i>
                        </summary>
                        <div class="cf-account-panel">
                            @auth
                                <div class="cf-account-name">
                                    <strong>{{ auth()->user()->name }}</strong>
                                    <small>{{ auth()->user()->email }}</small>
                                </div>
                                <a href="{{ route('claims.index') }}"><i class="bi bi-check2-circle"></i> My Claims</a>
                                <form method="post" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"><i class="bi bi-box-arrow-right"></i> Log Out</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Log In</a>
                                <a href="{{ route('register') }}"><i class="bi bi-person-plus"></i> Create Account</a>
                            @endauth
                        </div>
                    </details>
                @endif
            </div>
        </div>
    </div>
</header>
