@props([
    'item',
    'modalId' => null,
    'statusLabel' => null,
    'statusClass' => null,
    'dateValue' => null,
    'buttonLabel' => 'Details',
])

@php
    $hasImage = !empty($item['has_image']) && !empty($item['image_url']);
    $dateLabel = \Illuminate\Support\Carbon::parse($dateValue ?? $item['created_at'])->diffForHumans();
    $displayStatusLabel = $statusLabel ?? ucfirst($item['status']);
    $displayStatusClass = $statusClass ?? $item['status'];
    $categoryIcons = [
        'ticket' => 'bi-ticket-perforated',
        'id_card' => 'bi-person-vcard',
        'bottle_umbrella' => 'bi-umbrella',
        'electronic' => 'bi-laptop',
        'wallet' => 'bi-wallet2',
        'key' => 'bi-key',
        'book' => 'bi-journal-text',
        'clothes_accessories' => 'bi-backpack',
        'other' => 'bi-box-seam',
    ];
    $placeholderIcon = $categoryIcons[$item['category'] ?? 'other'] ?? 'bi-box-seam';
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
            <div class="cf-card-placeholder cf-card-placeholder-{{ $item['category'] ?? 'other' }}">
                <i class="bi {{ $placeholderIcon }}"></i>
            </div>
        @endif
        <span class="cf-status cf-status-{{ $displayStatusClass }}">{{ $displayStatusLabel }}</span>
    </div>
    <div class="cf-report-body">
        <div class="cf-card-topline">{{ $item['category_label'] ?? 'Other' }}</div>
        <h3>{{ $item['title'] }}</h3>
        <p><i class="bi bi-geo-alt"></i>{{ $item['location'] }}</p>
        <p><i class="bi bi-calendar3"></i>{{ $dateLabel }}</p>
        @if($modalId)
            <button type="button" class="cf-btn cf-btn-outline mt-auto" data-cf-card-button data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                {{ $buttonLabel }}
            </button>
        @else
            <a href="{{ route('board.index') }}" class="cf-btn cf-btn-outline mt-auto">View</a>
        @endif

        @if (auth()->user()?->isAdmin())
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
