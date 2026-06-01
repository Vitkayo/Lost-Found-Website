<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard — Lost & Found</title>
    <link href="{{ asset('assets/bootstrap-5.3.3/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/lostfound.css') }}" rel="stylesheet">
</head>
<body class="bg-light admin-shell">
    @include('admin.partials.sidebar')

    <div class="admin-main flex-grow-1" style="margin-left: 260px;">
        <div class="p-3 p-md-4 min-vh-100">
            @if (session('success'))
                <div class="alert alert-success border-2 border-dark fw-bold py-2">{{ session('success') }}</div>
            @endif

            <div class="mb-4 text-center">
                <h1 class="fw-bold text-dark h3 mb-1">Management Dashboard</h1>
                <p class="text-muted small mb-0">Monitor reports, categories, and community claims.</p>
            </div>

            <div class="row g-2 g-md-3 mb-4">
                <div class="col-6 col-lg">
                    <div class="card border-0 shadow-sm admin-stat-card bg-primary text-white p-3">
                        <small class="text-uppercase opacity-75">Items</small>
                        <h4 class="fw-bold m-0">{{ $totalItems }}</h4>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="card border-0 shadow-sm admin-stat-card bg-danger text-white p-3">
                        <small class="text-uppercase opacity-75">Lost</small>
                        <h4 class="fw-bold m-0">{{ $lostItems }}</h4>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="card border-0 shadow-sm admin-stat-card bg-success text-white p-3">
                        <small class="text-uppercase opacity-75">Found</small>
                        <h4 class="fw-bold m-0">{{ $foundItems }}</h4>
                    </div>
                </div>
                <div class="col-6 col-lg">
                    <div class="card border-0 shadow-sm admin-stat-card bg-dark text-white p-3">
                        <small class="text-uppercase opacity-75">Claims</small>
                        <h4 class="fw-bold m-0">{{ $totalClaims }}</h4>
                    </div>
                </div>
            </div>

            <ul class="nav nav-pills admin-section-tabs justify-content-center gap-2 mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ $section === 'items' ? 'active' : '' }}"
                       href="{{ route('admin.dashboard', ['section' => 'items', 'search' => $search, 'status' => $status !== 'all' ? $status : null, 'category' => $category !== 'all' ? $category : null, 'sort' => $sort]) }}">
                        Report Items ({{ $totalItems }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $section === 'claims' ? 'active' : '' }}"
                       href="{{ route('admin.dashboard', ['section' => 'claims', 'search' => $search, 'category' => $category !== 'all' ? $category : null, 'sort' => $sort, 'claim_status' => $claimFilter]) }}">
                        Claim Items ({{ $totalClaims }})
                    </a>
                </li>
            </ul>

            <form method="get" action="{{ route('admin.dashboard') }}" class="card border-0 shadow-sm mb-3 rounded-4">
                <input type="hidden" name="section" value="{{ $section }}">
                @if($section === 'claims')
                    <input type="hidden" name="claim_status" value="{{ $claimFilter }}">
                @else
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="card-body d-flex flex-wrap gap-2 align-items-center p-3">
                    <input type="text" name="search" value="{{ $search }}"
                           class="form-control flex-grow-1 border bg-white py-2"
                           style="min-width: 160px; font-size: 0.875rem;"
                           placeholder="Search...">
                    <select name="category" class="form-select border bg-white py-2" style="width: auto; min-width: 180px; font-size: 0.875rem;" onchange="this.form.submit()">
                        <option value="all" @selected($category === 'all')>All categories</option>
                        @foreach($categories as $slug => $label)
                            <option value="{{ $slug }}" @selected($category === $slug)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="sort" class="form-select border bg-white py-2" style="width: auto; font-size: 0.875rem;" onchange="this.form.submit()">
                        <option value="desc" @selected($sort === 'desc')>Newest first</option>
                        <option value="asc" @selected($sort === 'asc')>Oldest first</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold rounded-pill px-3">Apply</button>
                </div>
            </form>

            @if($section === 'claims')
                @php
                    $adminClaimsQuery = fn (array $overrides = []) => array_filter(array_merge([
                        'section' => 'claims',
                        'claim_status' => $claimFilter !== 'all' ? $claimFilter : null,
                        'category' => $category !== 'all' ? $category : null,
                        'search' => $search ?: null,
                        'sort' => $sort,
                    ], $overrides), fn ($v) => $v !== null && $v !== '');
                @endphp
                <div class="lf-filter-bar mb-3" style="max-width: 360px;">
                    @foreach(['all', 'return', 'claim'] as $type)
                        @php $active = $claimFilter === $type; @endphp
                        <a href="{{ route('admin.dashboard', $adminClaimsQuery(['claim_status' => $type !== 'all' ? $type : null])) }}"
                           class="lf-filter-pill lf-filter-{{ $type }} {{ $active ? 'active' : '' }}">
                            {{ $type }}
                        </a>
                    @endforeach
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3 py-2">Type</th>
                                    <th>Item</th>
                                    <th class="d-none d-md-table-cell">Category</th>
                                    <th>Contact</th>
                                    <th class="d-none d-lg-table-cell">Message</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($claims as $claim)
                                    <tr @if(!empty($claim['item'])) class="admin-clickable-row" data-admin-row-modal="#admin-claim-item-{{ $claim['id'] }}" tabindex="0" @endif>
                                        <td class="ps-3">
                                            <span class="badge rounded-pill {{ $claim['type'] === 'claim' ? 'bg-primary' : 'bg-success' }}">
                                                {{ $claim['type_label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $claim['item']['title'] ?? '—' }}</div>
                                            <small class="text-muted">📍 {{ $claim['item']['location'] ?? '' }}</small>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="badge bg-light text-dark border">{{ $claim['item']['category_label'] ?? 'Other' }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $claim['claimant_name'] }}</div>
                                            <small>{{ $claim['contact_info'] }}</small>
                                        </td>
                                        <td class="d-none d-lg-table-cell text-muted">{{ \Illuminate\Support\Str::limit($claim['message'] ?: '—', 40) }}</td>
                                        <td class="text-end pe-3">
                                            @if(!empty($claim['item']))
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1"
                                                        data-bs-toggle="modal" data-bs-target="#admin-claim-item-{{ $claim['id'] }}">
                                                    View
                                                </button>
                                            @endif
                                            <form method="post" action="{{ route('admin.claims.destroy', $claim['id']) }}" class="d-inline"
                                                  onsubmit="return confirm('Remove this claim?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No claims yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                @php
                    $adminItemsQuery = fn (array $overrides = []) => array_filter(array_merge([
                        'section' => 'items',
                        'status' => $status !== 'all' ? $status : null,
                        'category' => $category !== 'all' ? $category : null,
                        'search' => $search ?: null,
                        'sort' => $sort,
                    ], $overrides), fn ($v) => $v !== null && $v !== '');
                @endphp
                <div class="lf-filter-bar mb-3" style="max-width: 360px;">
                    @foreach(['all', 'lost', 'found'] as $type)
                        @php $active = $status === $type; @endphp
                        <a href="{{ route('admin.dashboard', $adminItemsQuery(['status' => $type !== 'all' ? $type : null])) }}"
                           class="lf-filter-pill lf-filter-{{ $type }} {{ $active ? 'active' : '' }}">
                            {{ $type }}
                        </a>
                    @endforeach
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3 py-2">Item</th>
                                    <th>Status</th>
                                    <th class="d-none d-md-table-cell">Category</th>
                                    <th class="d-none d-lg-table-cell">Location</th>
                                    <th class="d-none d-sm-table-cell">Date</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr class="admin-clickable-row" data-admin-row-modal="#admin-item-{{ $item['id'] }}" tabindex="0">
                                        <td class="ps-3">
                                            <div class="fw-bold text-primary text-uppercase">{{ $item['title'] }}</div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill {{ $item['status'] === 'lost' ? 'bg-danger' : 'bg-success' }}">
                                                {{ strtoupper($item['status']) }}
                                            </span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="badge bg-light text-dark border">{{ $item['category_label'] ?? 'Other' }}</span>
                                        </td>
                                        <td class="d-none d-lg-table-cell text-muted">{{ $item['location'] }}</td>
                                        <td class="d-none d-sm-table-cell text-muted">
                                            {{ \Illuminate\Support\Carbon::parse($item['created_at'])->toFormattedDateString() }}
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1"
                                                    data-bs-toggle="modal" data-bs-target="#admin-item-{{ $item['id'] }}">
                                                View
                                            </button>
                                            <form method="post" action="{{ route('admin.items.destroy', $item['id']) }}" class="d-inline"
                                                  onsubmit="return confirm('Delete this report?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No reports yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @foreach($items as $item)
        <div class="modal fade" id="admin-item-{{ $item['id'] }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border border-3 border-dark" style="border-radius: 20px; overflow: hidden;">
                    <div class="modal-body p-0">
                        <div class="bg-dark d-flex align-items-center justify-content-center" style="height: 220px;">
                            @if(!empty($item['has_image']) && !empty($item['image_url']))
                                <img src="{{ $item['image_url'] }}"
                                     class="h-100 mw-100" style="object-fit: contain;" alt="">
                            @else
                                <span class="text-white-50 small fw-semibold">No photo</span>
                            @endif
                        </div>
                        <div class="p-3">
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <span class="badge bg-primary">{{ $item['category_label'] ?? 'Other' }}</span>
                                <span class="badge {{ $item['status'] === 'lost' ? 'bg-danger' : 'bg-success' }}">{{ strtoupper($item['status']) }}</span>
                            </div>
                            <h2 class="fw-bold h5 text-uppercase">{{ $item['title'] }}</h2>
                            <p class="text-muted small fw-bold mb-2">📍 {{ $item['location'] }}</p>
                            <p class="bg-light p-2 rounded-3 border small mb-2">{{ $item['description'] ?: 'No description.' }}</p>
                            <p class="small fw-bold mb-0">Contact: {{ $item['contact_info'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @if($section === 'claims')
        @foreach($claims as $claim)
            @if(!empty($claim['item']))
            @php $item = $claim['item']; @endphp
            <div class="modal fade" id="admin-claim-item-{{ $claim['id'] }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border border-3 border-dark" style="border-radius: 20px; overflow: hidden;">
                        <div class="modal-body p-0">
                            <div class="bg-dark d-flex align-items-center justify-content-center" style="height: 220px;">
                                @if(!empty($item['has_image']) && !empty($item['image_url']))
                                    <img src="{{ $item['image_url'] }}"
                                         class="h-100 mw-100" style="object-fit: contain;" alt="">
                                @else
                                    <span class="text-white-50 small fw-semibold">No photo</span>
                                @endif
                            </div>
                            <div class="p-3">
                                <div class="d-flex gap-2 flex-wrap mb-2">
                                    <span class="badge bg-primary">{{ $item['category_label'] ?? 'Other' }}</span>
                                    <span class="badge {{ $item['status'] === 'lost' ? 'bg-danger' : 'bg-success' }}">{{ strtoupper($item['status']) }}</span>
                                    <span class="badge bg-dark">{{ $claim['type_label'] }}</span>
                                </div>
                                <h2 class="fw-bold h5 text-uppercase">{{ $item['title'] }}</h2>
                                <p class="text-muted small fw-bold mb-2">Location: {{ $item['location'] }}</p>
                                <p class="bg-light p-2 rounded-3 border small mb-2">{{ $item['description'] ?: 'No description.' }}</p>
                                <p class="small fw-bold mb-1">Reporter contact: {{ $item['contact_info'] }}</p>
                                <p class="small fw-bold mb-1">Requester: {{ $claim['claimant_name'] }}</p>
                                <p class="small fw-bold mb-2">Requester contact: {{ $claim['contact_info'] }}</p>
                                <p class="bg-light p-2 rounded-3 border small mb-0">{{ $claim['message'] ?: 'No message provided.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    @endif

    <script src="{{ asset('assets/bootstrap-5.3.3/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.addEventListener('click', function (event) {
            if (event.target.closest('a, button, input, select, textarea, form')) {
                return;
            }

            const row = event.target.closest('[data-admin-row-modal]');
            if (!row) {
                return;
            }

            const modal = document.querySelector(row.dataset.adminRowModal);
            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (!['Enter', ' '].includes(event.key)) {
                return;
            }

            const row = event.target.closest('[data-admin-row-modal]');
            if (!row || event.target !== row) {
                return;
            }

            event.preventDefault();
            const modal = document.querySelector(row.dataset.adminRowModal);
            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });
    </script>
</body>
</html>
