@extends('layouts.main')

@section('title', 'Campus Found')

@php
    $categoryIcons = [
        'ticket' => 'bi-ticket-perforated',
        'bottle_umbrella' => 'bi-umbrella',
        'electronic' => 'bi-laptop',
        'id_card' => 'bi-person-vcard',
        'wallet' => 'bi-wallet2',
        'key' => 'bi-key',
        'book' => 'bi-journal-text',
        'clothes_accessories' => 'bi-backpack',
        'other' => 'bi-three-dots',
    ];
@endphp

@section('content')
<div class="cf-page">
    <main>
        <section class="cf-hero">
            <div class="cf-container cf-hero-grid">
                <div class="cf-hero-copy">
                    <h1>Lost Something <span>on Campus?</span></h1>
                    <p>
                        Search lost and found reports submitted by students and staff.
                        Reclaim your valuables through our high-trust university network.
                    </p>
                    <form class="cf-search" action="{{ route('board.index') }}" method="get">
                        <label class="cf-search-box">
                            <i class="bi bi-search"></i>
                            <input name="search" type="search" placeholder="Search for items (e.g. Blue Airpods)">
                        </label>
                        <button type="submit" class="cf-btn cf-btn-primary">Browse Items</button>
                    </form>
                    <div class="cf-popular">
                        <span>Popular:</span>
                        <a href="{{ route('board.index', ['category' => 'ticket']) }}">Tickets</a>
                        <a href="{{ route('board.index', ['category' => 'id_card']) }}">Card / ID</a>
                        <a href="{{ route('board.index', ['category' => 'bottle_umbrella']) }}">Umbrellas</a>
                    </div>
                </div>
                <div class="cf-hero-art" aria-hidden="true">
                    <img src="{{ asset('assets/campus-found-hero.png') }}" alt="">
                </div>
            </div>
        </section>

        <section class="cf-metrics" aria-label="Campus Found metrics">
            <div class="cf-container cf-metric-grid">
                <div><strong>{{ number_format($stats['reported']) }}</strong><span>Items Reported</span></div>
                <div><strong>{{ number_format($stats['found']) }}</strong><span>Found Items</span></div>
                <div><strong>{{ number_format($stats['lost']) }}</strong><span>Lost Items</span></div>
                <div><strong>{{ number_format($stats['active']) }}</strong><span>Active Reports</span></div>
            </div>
        </section>

        <section class="cf-section cf-section-muted">
            <div class="cf-container">
                <div class="cf-section-head">
                    <div>
                        <h2>Recent Lost &amp; Found Reports</h2>
                        <p>See the latest items reported by the campus community.</p>
                    </div>
                    <a href="{{ route('board.index') }}" class="cf-link-action">
                        View All Reports <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="cf-report-grid">
                    @forelse($recentItems as $item)
                        @php $modalId = 'home-item-' . $item['id']; @endphp
                        @include('partials.item-card', ['item' => $item, 'modalId' => $modalId])
                        @include('partials.item-modal', ['item' => $item, 'id' => $modalId])
                    @empty
                        <div class="cf-empty-state">
                            <h2>No reports yet.</h2>
                            <p>Be the first to report a lost or found campus item.</p>
                            <a href="{{ route('report.create') }}" class="cf-btn cf-btn-primary">Report Item</a>
                        </div>
                    @endforelse
                </div>

                @if(!empty($recentClaims))
                    <section class="cf-home-claimed-section" aria-labelledby="home-recent-claims-title">
                        <div class="cf-section-head">
                            <div>
                                <h2 id="home-recent-claims-title">Recently Claimed</h2>
                                <p>If an item is no longer in the recent reports, check here first.</p>
                            </div>
                            <a href="{{ route('claims.index') }}" class="cf-link-action">
                                View Claimed Items <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="cf-report-grid">
                            @foreach($recentClaims as $claim)
                                @php $claimItem = $claim['item'] ?? null; @endphp
                                @if($claimItem)
                                    @php $claimModalId = 'home-claimed-item-' . $claim['id']; @endphp
                                    @include('partials.item-card', [
                                        'item' => $claimItem,
                                        'modalId' => $claimModalId,
                                        'statusLabel' => 'Claimed',
                                        'statusClass' => 'claimed',
                                        'dateValue' => $claim['created_at'],
                                        'buttonLabel' => 'View Details',
                                    ])
                                    @include('partials.item-modal', [
                                        'item' => $claimItem,
                                        'id' => $claimModalId,
                                        'showAction' => false,
                                        'showAdminDelete' => false,
                                    ])
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </section>

        <section class="cf-section">
            <div class="cf-container">
                <div class="cf-section-head">
                    <div>
                        <h2>Explore by Category</h2>
                        <p>Browse common RUPP item types to find your belongings.</p>
                    </div>
                    <a href="{{ route('board.index') }}" class="cf-link-action">
                        View All Categories <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="cf-category-grid">
                    @foreach($categories as $slug => $label)
                        <a href="{{ route('board.index', ['category' => $slug]) }}" class="cf-category-card">
                            <span><i class="bi {{ $categoryIcons[$slug] ?? 'bi-three-dots' }}"></i></span>
                            <strong>{{ $label }}</strong>
                            <small>{{ number_format($categoryCounts[$slug] ?? 0) }} items</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="cf-section" id="how-it-works">
            <div class="cf-container">
                <div class="cf-steps-head">
                    <h2>Get Your Items Back in 3 Steps</h2>
                </div>
                <div class="cf-steps">
                    <div class="cf-step">
                        <div class="cf-step-icon"><i class="bi bi-search"></i><span>1</span></div>
                        <h3>Search</h3>
                        <p>Look through the active database of found items across campus locations.</p>
                    </div>
                    <div class="cf-step">
                        <div class="cf-step-icon"><i class="bi bi-exclamation-triangle"></i><span>2</span></div>
                        <h3>Report</h3>
                        <p>Lost or found something? Create a report so the right person can respond.</p>
                    </div>
                    <div class="cf-step">
                        <div class="cf-step-icon"><i class="bi bi-check2-circle"></i><span>3</span></div>
                        <h3>Recover</h3>
                        <p>Submit a claim or found report and coordinate with the reporter through contact details.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="cf-footer">
        <div class="cf-container cf-footer-grid">
            <div>
                <h2>Campus Found</h2>
                <p>Connecting the campus community through honesty and technology. Lost and found made simple.</p>
            </div>
            <div>
                <h3>Navigation</h3>
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('board.index') }}">Board</a>
                <a href="{{ route('claims.index') }}">Claims</a>
                <a href="{{ route('report.create') }}">Report Item</a>
            </div>
            <div>
                <h3>Categories</h3>
                <a href="{{ route('board.index', ['category' => 'electronic']) }}">Electronics</a>
                <a href="{{ route('board.index', ['category' => 'ticket']) }}">Tickets</a>
                <a href="{{ route('board.index', ['category' => 'id_card']) }}">Card / ID</a>
                <a href="{{ route('board.index', ['category' => 'key']) }}">Keys</a>
                <a href="{{ route('board.index', ['category' => 'book']) }}">Books / Documents</a>
            </div>
            <div>
                <h3>Newsletter</h3>
                <p>Stay updated with campus safety news.</p>
                <form class="cf-newsletter">
                    <input type="email" placeholder="Email address">
                    <button type="button">Join</button>
                </form>
            </div>
        </div>
        <div class="cf-container cf-copyright">© 2026 Campus Found. All rights reserved.</div>
    </footer>
</div>
@endsection
