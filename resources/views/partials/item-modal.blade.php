@props(['item', 'id', 'showAction' => true, 'showAdminDelete' => true])

@php
    $isFound = $item['status'] === 'found';
    $successMessage = $isFound
        ? 'Claim submitted. The reporter will review your private proof.'
        : 'Found-item report submitted. The owner will review it.';
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

                @if(!empty($item['can_manage']) || auth()->user()?->isAdmin())
                    <div class="cf-detail-block cf-contact-block">
                        <h3>Private Contact Details</h3>
                        <p>{{ $item['contact_info'] }}</p>
                    </div>
                    @if(!empty($item['hidden_details']))
                        <div class="cf-detail-block">
                            <h3>Hidden Identifying Details</h3>
                            <p>{{ $item['hidden_details'] }}</p>
                        </div>
                    @endif
                @endif

                @if($showAction && auth()->check() && empty($item['can_manage']))
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
                        <form method="post" action="{{ route('claims.store') }}" class="cf-modal-form" enctype="multipart/form-data" data-cf-claim-form data-success-message="{{ $successMessage }}">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item['id'] }}">
                            <input type="hidden" name="modal_flow" value="1">
                            <label>
                                <span>Name <small>optional</small></span>
                                <input type="text" name="claimant_name" placeholder="Your name">
                            </label>
                            @if($isFound)
                                <label>
                                    <span>How can you prove this is yours? <strong>*</strong></span>
                                    <textarea name="ownership_proof" rows="3" placeholder="Describe a private detail, such as a scratch, contents, serial number, or lock-screen image." required></textarea>
                                    <small class="cf-field-help">Only the reporter and administrators can review this proof.</small>
                                </label>
                                <label>
                                    <span>Proof Photo <small>optional</small></span>
                                    <input type="file" name="proof_image" accept="image/jpeg,image/png,image/webp">
                                    <small class="cf-field-help">Upload a receipt or another supporting image. Maximum 5 MB.</small>
                                </label>
                            @endif
                            <label>
                                <span>Contact Method <strong>*</strong></span>
                                <input type="text" name="contact_info" placeholder="Phone number, email, or Telegram" required>
                            </label>
                            @unless($isFound)
                                <label>
                                    <span>Where and when did you find it? <strong>*</strong></span>
                                    <textarea name="ownership_proof" rows="3" placeholder="Share the location, date, and how the owner can identify it." required></textarea>
                                </label>
                            @endunless
                            <button type="submit" class="cf-btn cf-btn-primary">
                                {{ $isFound ? 'Submit Claim' : 'Submit Found Report' }}
                            </button>
                        </form>
                        <div class="cf-inline-success d-none" role="status"></div>
                    </div>
                @elseif($showAction && !auth()->check())
                    <div class="cf-modal-action">
                        <a href="{{ route('login') }}" class="cf-btn cf-btn-primary">Login to Respond</a>
                    </div>
                @endif

                @if(!empty($item['can_manage']))
                    <div class="cf-owner-actions">
                        <a href="{{ route('report.edit', $item['id']) }}" class="cf-btn cf-btn-outline">Edit Report</a>
                        <form method="post" action="{{ route('report.destroy', $item['id']) }}" onsubmit="return confirm('Delete this report permanently?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="cf-btn cf-btn-danger">Delete Report</button>
                        </form>
                    </div>
                @endif

                @if ($showAdminDelete && auth()->user()?->isAdmin())
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
