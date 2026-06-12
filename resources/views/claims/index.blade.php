@extends('layouts.main')

@section('title', 'Claims')

@section('content')
@php
    $claimsQuery = fn (array $overrides = []) => array_filter(array_merge([
        'type' => $filter !== 'all' ? $filter : null,
        'search' => $search ?: null,
        'sort' => $sort,
    ], $overrides), fn ($v) => $v !== null && $v !== '');
@endphp

<div class="cf-page cf-claims-page">
    <section class="cf-board-hero">
        <div class="cf-container">
            <h1>Claims</h1>
            <p>Track submitted claims and found-item reports.</p>
        </div>
    </section>

    <section class="cf-container cf-board-shell">
        <form method="get" action="{{ route('claims.index') }}" class="cf-board-search">
            <input type="hidden" name="type" value="{{ $filter }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <label class="cf-search-box">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="Search item, name, contact, or message...">
            </label>
            <button type="submit" class="cf-btn cf-btn-primary">Search</button>
        </form>

        <div class="cf-claims-filter-bar">
            <div class="cf-filter-row" aria-label="Claim request type filters">
                @foreach(['all' => 'All', 'return' => 'Found Reports', 'claim' => 'Claims'] as $value => $label)
                    <a href="{{ route('claims.index', $claimsQuery(['type' => $value === 'all' ? null : $value])) }}"
                       class="cf-filter-pill cf-filter-{{ $value }} {{ $filter === $value ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>

            <form method="get" class="cf-board-controls cf-claims-sort-control">
                <input type="hidden" name="type" value="{{ $filter }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <label>
                    <span>Sort</span>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="desc" @selected($sort === 'desc')>Newest first</option>
                        <option value="asc" @selected($sort === 'asc')>Oldest first</option>
                    </select>
                </label>
            </form>
        </div>

        <div class="cf-claim-grid">
            @forelse($claims as $claim)
                @php
                    $claimItem = $claim['item'] ?? null;
                    $modalId = $claimItem ? 'claim-item-' . $claim['id'] : null;
                @endphp
                <article class="cf-claim-card">
                    <div class="cf-claim-card-head">
                        <span class="cf-request-badge cf-request-{{ $claim['type_class'] }}">{{ $claim['type_label'] }}</span>
                        <small>{{ \Illuminate\Support\Carbon::parse($claim['created_at'])->diffForHumans() }}</small>
                    </div>
                    <h3>{{ $claim['item']['title'] ?? 'Unknown item' }}</h3>
                    <p><span>Claimant</span>{{ $claim['claimant_name'] }}</p>
                    @if($claim['status'] === 'approved' || !empty($claim['can_review']) || (auth()->check() && (int) auth()->id() === (int) ($claim['user_id'] ?? 0)))
                        <p><span>Contact</span>{{ $claim['contact_info'] }}</p>
                    @endif
                    <div class="cf-message-preview">{{ $claim['message'] ?: 'No message provided.' }}</div>
                    <span class="cf-request-badge cf-request-{{ $claim['status_class'] }}">{{ $claim['status_label'] }}</span>

                    @if(!empty($claim['can_review']) && $claim['status'] === 'pending')
                        <div class="cf-claim-proof">
                            <strong>Verification answer</strong>
                            <p>{{ $claim['verification_answer'] ?: 'No answer provided.' }}</p>
                        </div>
                        <div class="cf-review-actions">
                            <form method="post" action="{{ route('claims.review', $claim['id']) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="cf-btn cf-btn-success" type="submit">Approve</button></form>
                            <form method="post" action="{{ route('claims.review', $claim['id']) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="cf-btn cf-btn-danger" type="submit">Reject</button></form>
                        </div>
                    @endif

                    @if($claimItem)
                        <button type="button" class="cf-btn cf-btn-outline w-100 mt-3" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                            View Details
                        </button>
                    @endif

                    @if (session('is_admin'))
                        <form method="post" action="{{ route('admin.claims.destroy', $claim['id']) }}" class="mt-2"
                              onsubmit="return confirm('Delete this claim permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="cf-btn cf-btn-danger w-100">Delete</button>
                        </form>
                    @endif
                </article>
                @if($claimItem)
                    @include('partials.item-modal', [
                        'item' => $claimItem,
                        'id' => $modalId,
                        'showAction' => false,
                        'showAdminDelete' => false,
                    ])
                @endif
            @empty
                <div class="cf-empty-state">
                    <h2>No claims yet.</h2>
                    <p>Open an item on the Board and submit a claim or found report from its details modal.</p>
                    <a href="{{ route('board.index') }}" class="cf-btn cf-btn-primary">Go to Board</a>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
