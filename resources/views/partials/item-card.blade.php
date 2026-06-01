@props(['item', 'modalId' => null])

@php
    $hasImage = !empty($item['has_image']) && !empty($item['image_url']);
    $dateLabel = \Illuminate\Support\Carbon::parse($item['created_at'])->diffForHumans();
@endphp

<article class="cf-report-card"
         data-cf-item-id="{{ $item['id'] }}"
         @if($modalId)
             role="button"
             tabindex="0"
             data-cf-card-open="#{{ $modalId }}"
             aria-label="View details for {{ $item['title'] }}"
         @endif>
    <div class="cf-report-media">
        @if($hasImage)
            <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}">
        @else
            <div class="cf-card-placeholder">
                <i class="bi bi-image"></i>
            </div>
        @endif
        <span class="cf-status cf-status-{{ $item['status'] }}">{{ ucfirst($item['status']) }}</span>
    </div>
    <div class="cf-report-body">
        <div class="cf-card-topline">{{ $item['category_label'] ?? 'Other' }}</div>
        <h3>{{ $item['title'] }}</h3>
        <p><i class="bi bi-geo-alt"></i>{{ $item['location'] }}</p>
        <p><i class="bi bi-calendar3"></i>{{ $dateLabel }}</p>
        @if($modalId)
            <button type="button" class="cf-btn cf-btn-outline mt-auto" data-cf-card-button data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                Details
            </button>
        @else
            <a href="{{ route('board.index') }}" class="cf-btn cf-btn-outline mt-auto">View</a>
        @endif

        @if (session('is_admin'))
            <form method="post"
                  action="{{ route('admin.items.destroy', $item['id']) }}"
                  class="cf-card-admin-actions"
                  data-cf-card-button
                  onsubmit="return confirm('Delete this report permanently?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="cf-btn cf-btn-danger w-100">Delete</button>
            </form>
        @endif
    </div>
</article>
