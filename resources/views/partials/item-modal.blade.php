@props(['item', 'id', 'showAction' => true, 'showAdminDelete' => true])

@php
    $isFound = $item['status'] === 'found';
    $successMessage = $isFound
        ? 'Claim submitted successfully. The reporter will contact you soon.'
        : 'Found report submitted successfully. The owner will contact you soon.';
@endphp

<div class="modal fade cf-item-modal" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <button type="button" class="cf-modal-close" data-bs-dismiss="modal" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>

            <div class="cf-modal-media">
                @if(!empty($item['has_image']) && !empty($item['image_url']))
                    <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}">
                @else
                    <div class="cf-card-placeholder"><i class="bi bi-image"></i></div>
                @endif
            </div>

            <div class="cf-modal-body">
                <div class="cf-modal-title-row">
                    <div>
                        <div class="cf-card-topline">{{ $item['category_label'] ?? 'Other' }}</div>
                        <h2>{{ $item['title'] }}</h2>
                    </div>
                    <div class="cf-modal-badges">
                        <span class="cf-status-chip">{{ $item['category_label'] ?? 'Other' }}</span>
                        <span class="cf-status cf-status-{{ $item['status'] }}">{{ ucfirst($item['status']) }}</span>
                    </div>
                </div>

                <div class="cf-modal-meta">
                    <span><i class="bi bi-calendar3"></i>{{ \Illuminate\Support\Carbon::parse($item['created_at'])->toFormattedDateString() }}</span>
                    <span><i class="bi bi-geo-alt"></i>{{ $item['location'] }}</span>
                </div>

                <div class="cf-detail-block">
                    <h3>Description</h3>
                    <p>{{ $item['description'] ?: 'No description provided.' }}</p>
                </div>

                <div class="cf-detail-block cf-contact-block">
                    <h3>Contact Details</h3>
                    <p>{{ $item['contact_info'] }}</p>
                </div>

                @if($showAction)
                    <div class="cf-modal-action">
                        <button type="button"
                                class="cf-btn {{ $isFound ? 'cf-btn-success' : 'cf-btn-warning' }}"
                                data-bs-toggle="collapse"
                                data-bs-target="#claim-form-{{ $id }}"
                                aria-expanded="false">
                            {{ $isFound ? 'Claim This Item' : 'Found This Item' }}
                        </button>
                        <p>
                            {{ $isFound
                                ? 'Think this is yours? Submit a claim with your contact info.'
                                : 'Found this lost item? Let the owner know how to reach you.' }}
                        </p>
                    </div>

                    <div class="collapse" id="claim-form-{{ $id }}">
                        <form method="post" action="{{ route('claims.store') }}" class="cf-modal-form" data-cf-claim-form data-success-message="{{ $successMessage }}">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item['id'] }}">
                            <input type="hidden" name="modal_flow" value="1">
                            <label>
                                <span>Name <small>optional</small></span>
                                <input type="text" name="claimant_name" placeholder="Your name">
                            </label>
                            <label>
                                <span>Your Contact <strong>*</strong></span>
                                <input type="text" name="contact_info" placeholder="Phone or Telegram" required>
                            </label>
                            <label>
                                <span>{{ $isFound ? 'Message/proof' : 'Message/location found' }} <strong>*</strong></span>
                                <textarea name="message" rows="3" placeholder="{{ $isFound ? 'Describe how you can prove ownership...' : 'Where/when you found it...' }}" required></textarea>
                            </label>
                            <button type="submit" class="cf-btn cf-btn-primary">
                                {{ $isFound ? 'Submit Claim' : 'Submit Found Report' }}
                            </button>
                        </form>
                        <div class="cf-inline-success d-none" role="status"></div>
                    </div>
                @endif

                @if ($showAdminDelete && session('is_admin'))
                    <form method="post"
                          action="{{ route('admin.items.destroy', $item['id']) }}"
                          class="mt-3"
                          onsubmit="return confirm('Delete this report permanently?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="cf-btn cf-btn-danger w-100">Delete Report</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
