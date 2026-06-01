@extends('layouts.main')

@section('title', 'Board')

@section('content')
@php
    $displayItems = collect($items);

    $boardQuery = fn (array $overrides = []) => array_filter(array_merge([
        'status' => $filter !== 'all' ? $filter : null,
        'category' => $category !== 'all' ? $category : null,
        'search' => $search ?: null,
        'date' => $date ?: null,
        'sort' => $sort,
    ], $overrides), fn ($v) => $v !== null && $v !== '');
@endphp

<div class="cf-page cf-board-page">
    <section class="cf-board-hero">
        <div class="cf-container">
            <h1>RUPP Community Board</h1>
            <p>Find your belongings within the RUPP community.</p>
        </div>
    </section>

    <section class="cf-container cf-board-shell">
        <form method="get" action="{{ route('board.index') }}" class="cf-board-search">
            <input type="hidden" name="status" value="{{ $filter }}">
            <input type="hidden" name="category" value="{{ $category }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <label class="cf-search-box">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="Search item name or location...">
            </label>
            <button type="submit" class="cf-btn cf-btn-primary">Search</button>
        </form>

        <div class="cf-filter-row" aria-label="Status filters">
            @foreach(['all' => 'All', 'lost' => 'Lost', 'found' => 'Found'] as $value => $label)
                <a href="{{ route('board.index', $boardQuery(['status' => $value === 'all' ? null : $value])) }}"
                   class="cf-filter-pill cf-filter-{{ $value }} {{ $filter === $value ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="cf-chip-row" aria-label="Category filters">
            <a href="{{ route('board.index', $boardQuery(['category' => null])) }}"
               class="cf-chip {{ $category === 'all' ? 'active' : '' }}">All Categories</a>
            @foreach($categories as $slug => $label)
                <a href="{{ route('board.index', $boardQuery(['category' => $slug])) }}"
                   class="cf-chip {{ $category === $slug ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <form method="get" class="cf-board-controls">
            <input type="hidden" name="status" value="{{ $filter }}">
            <input type="hidden" name="category" value="{{ $category }}">
            <input type="hidden" name="search" value="{{ $search }}">
            <label>
                <span>Date</span>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
            </label>
            <label>
                <span>Sort</span>
                <select name="sort" onchange="this.form.submit()">
                    <option value="desc" @selected($sort === 'desc')>Newest first</option>
                    <option value="asc" @selected($sort === 'asc')>Oldest first</option>
                </select>
            </label>
        </form>

        <div class="cf-report-grid">
            @forelse($displayItems as $item)
                @php $modalId = 'board-item-' . $item['id']; @endphp
                @include('partials.item-card', ['item' => $item, 'modalId' => $modalId])
                @include('partials.item-modal', ['item' => $item, 'id' => $modalId])
            @empty
                <div class="cf-empty-state">
                    <h2>No items match your filters.</h2>
                    <p>Try another category or report a new campus item.</p>
                    <a href="{{ route('report.create') }}" class="cf-btn cf-btn-primary">Report Item</a>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
