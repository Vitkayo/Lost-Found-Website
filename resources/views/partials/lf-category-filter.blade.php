{{-- Category chips for board --}}
@props(['category', 'categories', 'queryBuilder'])

<div class="lf-category-bar">
    @php $allCatActive = $category === 'all'; @endphp
    <a href="{{ route('board.index', $queryBuilder(['category' => null, 'sort' => config('lostfound.category_default_sort.all', 'desc')])) }}"
       class="lf-category-chip {{ $allCatActive ? 'active' : '' }}">
        All
    </a>
    @foreach($categories as $slug => $label)
        @php
            $catActive = $category === $slug;
            $catQuery = $queryBuilder([
                'category' => $slug,
                'sort' => config('lostfound.category_default_sort.'.$slug, 'desc'),
            ]);
        @endphp
        <a href="{{ route('board.index', $catQuery) }}"
           class="lf-category-chip {{ $catActive ? 'active' : '' }}">
            {{ $label }}
        </a>
    @endforeach
</div>
