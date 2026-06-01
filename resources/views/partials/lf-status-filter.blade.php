{{-- Status filter: ALL / LOST / FOUND --}}
@props(['filter', 'routeName', 'queryBuilder'])

<div class="lf-filter-bar px-2">
    @foreach(['all' => '#212529', 'lost' => '#dc3545', 'found' => '#198754'] as $type => $color)
        @php
            $active = $filter === $type;
            $query = $queryBuilder(['status' => $type !== 'all' ? $type : null]);
        @endphp
        <a href="{{ route($routeName, $query) }}"
           class="lf-filter-pill"
           style="border-color: {{ $color }};
                  background-color: {{ $active ? $color : '#fff' }};
                  color: {{ $active ? '#fff' : $color }};">
            {{ $type }}
        </a>
    @endforeach
</div>
