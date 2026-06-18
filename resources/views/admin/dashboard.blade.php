<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Campus Found</title>
    <link href="/assets/bootstrap-5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/lostfound.css?v=20260618-6" rel="stylesheet">
</head>
<body class="bg-light admin-shell">
    @include('admin.partials.sidebar')

    <div class="admin-main flex-grow-1" style="margin-left: 260px;">
        <div class="p-3 p-md-4 min-vh-100">
            @if (session('success'))
                <div class="alert alert-success border-2 border-dark fw-bold py-2" data-auto-dismiss>{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger border-2 border-dark fw-bold py-2" data-auto-dismiss>{{ session('error') }}</div>
            @endif

            <div class="admin-dashboard-heading mb-4 text-center">
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
                <div class="col-6 col-lg">
                    <div class="card border-0 shadow-sm admin-stat-card bg-warning text-dark p-3">
                        <small class="text-uppercase opacity-75">Pending / Disputed</small>
                        <h4 class="fw-bold m-0">{{ $pendingClaims }} / {{ $openDisputes }}</h4>
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
                       href="{{ route('admin.dashboard', ['section' => 'claims', 'search' => $search, 'category' => $category !== 'all' ? $category : null, 'sort' => $sort, 'claim_status' => $claimFilter, 'review_status' => $reviewStatus]) }}">
                        Claims ({{ $totalClaims }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $section === 'users' ? 'active' : '' }}"
                       href="{{ route('admin.dashboard', ['section' => 'users']) }}">
                        Users ({{ $totalUsers }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $section === 'audit' ? 'active' : '' }}"
                       href="{{ route('admin.dashboard', ['section' => 'audit']) }}">
                        Audit Log
                    </a>
                </li>
            </ul>

            @if(in_array($section, ['items', 'claims', 'users'], true))
            <form method="get" action="{{ route('admin.dashboard') }}" class="card border-0 shadow-sm mb-3 rounded-4">
                <input type="hidden" name="section" value="{{ $section }}">
                @if($section === 'claims')
                    <input type="hidden" name="claim_status" value="{{ $claimFilter }}">
                @elseif($section === 'items')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="card-body d-flex flex-wrap gap-2 align-items-center p-3">
                    <input type="text" name="search" value="{{ $search }}"
                           class="form-control flex-grow-1 border bg-white py-2"
                           style="min-width: 160px; font-size: 0.875rem;"
                           placeholder="Search...">
                    @if(in_array($section, ['items', 'claims'], true))
                    <select name="category" class="form-select border bg-white py-2" style="width: auto; min-width: 180px; font-size: 0.875rem;" onchange="this.form.submit()">
                        <option value="all" @selected($category === 'all')>All categories</option>
                        @foreach($categories as $slug => $label)
                            <option value="{{ $slug }}" @selected($category === $slug)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @endif
                    <select name="sort" class="form-select border bg-white py-2" style="width: auto; font-size: 0.875rem;" onchange="this.form.submit()">
                        <option value="desc" @selected($sort === 'desc')>Newest first</option>
                        <option value="asc" @selected($sort === 'asc')>Oldest first</option>
                    </select>
                    @if($section === 'claims')
                        <select name="review_status" class="form-select border bg-white py-2" style="width: auto; font-size: 0.875rem;" onchange="this.form.submit()">
                            <option value="all" @selected($reviewStatus === 'all')>All review statuses</option>
                        <option value="pending" @selected($reviewStatus === 'pending')>Pending review</option>
                        <option value="approved" @selected($reviewStatus === 'approved')>Approved</option>
                        <option value="rejected" @selected($reviewStatus === 'rejected')>Rejected</option>
                        </select>
                    @elseif($section === 'users')
                        <select name="user_role" class="form-select border bg-white py-2" style="width: auto; min-width: 150px; font-size: 0.875rem;" onchange="this.form.submit()">
                            <option value="all" @selected($userRole === 'all')>All roles</option>
                            <option value="user" @selected($userRole === 'user')>Users</option>
                            <option value="admin" @selected($userRole === 'admin')>Admins</option>
                            <option value="super_admin" @selected($userRole === 'super_admin')>Super admins</option>
                        </select>
                        <select name="user_status" class="form-select border bg-white py-2" style="width: auto; min-width: 150px; font-size: 0.875rem;" onchange="this.form.submit()">
                            <option value="all" @selected($userStatus === 'all')>All statuses</option>
                            <option value="active" @selected($userStatus === 'active')>Active</option>
                            <option value="suspended" @selected($userStatus === 'suspended')>Suspended</option>
                        </select>
                        <select name="user_verification" class="form-select border bg-white py-2" style="width: auto; min-width: 170px; font-size: 0.875rem;" onchange="this.form.submit()">
                            <option value="all" @selected($userVerification === 'all')>All verification</option>
                            <option value="verified" @selected($userVerification === 'verified')>Verified</option>
                            <option value="unverified" @selected($userVerification === 'unverified')>Unverified</option>
                        </select>
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm fw-bold rounded-pill px-3">Apply</button>
                </div>
            </form>
            @endif

            @if($section === 'users')
                @php $canManageUsers = auth()->user()?->isSuperAdmin() === true; @endphp
                <div class="row g-2 g-md-3 mb-3">
                    <div class="col-6 col-xl-3">
                        <div class="card border-0 shadow-sm p-3 h-100">
                            <small class="text-uppercase text-muted">Total users</small>
                            <div class="fw-bold fs-4">{{ $totalUsers }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card border-0 shadow-sm p-3 h-100">
                            <small class="text-uppercase text-muted">Active / Suspended</small>
                            <div class="fw-bold fs-4">{{ $activeUsers }} / {{ $suspendedUsers }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card border-0 shadow-sm p-3 h-100">
                            <small class="text-uppercase text-muted">Admins</small>
                            <div class="fw-bold fs-4">{{ $adminUsers }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-3">
                        <div class="card border-0 shadow-sm p-3 h-100">
                            <small class="text-uppercase text-muted">Unverified</small>
                            <div class="fw-bold fs-4">{{ $unverifiedUsers }}</div>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-dark"><tr><th class="ps-3">User</th><th>Student ID</th><th>Email</th><th>Joined</th><th>Access</th><th>Support</th>@if($canManageUsers)<th class="text-end pe-3">Update</th>@endif</tr></thead>
                            <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="ps-3"><strong>{{ $user->name }}</strong><br><small>{{ $user->email }}</small></td>
                                    <td>{{ $user->student_id ?: '-' }}</td>
                                    <td>
                                        @if($user->hasVerifiedEmail())
                                            <span class="badge bg-success">Verified</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Unverified</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->toFormattedDateString() }}</td>
                                    <td><span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($user->status) }}</span> <span class="badge bg-primary">{{ str_replace('_', ' ', ucfirst($user->role)) }}</span></td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            @unless($user->hasVerifiedEmail())
                                                <form method="post" action="{{ route('admin.users.verification.send', $user) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-primary w-100">Send verification</button>
                                                </form>
                                            @endunless
                                            <form method="post" action="{{ route('admin.users.password-reset.send', $user) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-secondary w-100">Send reset code</button>
                                            </form>
                                        </div>
                                    </td>
                                    @if($canManageUsers)
                                    <td class="text-end pe-3">
                                        <form method="post" action="{{ route('admin.users.update', $user) }}" class="d-flex justify-content-end gap-2">
                                            @csrf @method('PATCH')
                                            <select name="role" class="form-select form-select-sm" style="max-width: 130px" @disabled(auth()->user()?->is($user))>
                                                @foreach(['user' => 'User', 'admin' => 'Admin', 'super_admin' => 'Super Admin'] as $value => $label)
                                                    <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <select name="status" class="form-select form-select-sm" style="max-width: 120px" @disabled(auth()->user()?->is($user))>
                                                <option value="active" @selected($user->status === 'active')>Active</option>
                                                <option value="suspended" @selected($user->status === 'suspended')>Suspended</option>
                                            </select>
                                            <button class="btn btn-sm btn-primary" @disabled(auth()->user()?->is($user))>Save</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $canManageUsers ? 7 : 6 }}" class="text-center py-4">No users found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-3">{{ $users->withQueryString()->links() }}</div>
            @elseif($section === 'audit')
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-dark"><tr><th class="ps-3">Time</th><th>Actor</th><th>Action</th><th>Subject</th><th>Details</th></tr></thead>
                            <tbody>
                            @forelse($auditLogs as $log)
                                <tr><td class="ps-3">{{ $log->created_at->format('M j, Y H:i') }}</td><td>{{ $log->actor }}</td><td><strong>{{ $log->action }}</strong></td><td>{{ $log->subject_type }} #{{ $log->subject_id }}</td><td>{{ $log->details ?: '-' }}</td></tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4">No administrative activity yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-3">{{ $auditLogs->withQueryString()->links() }}</div>
            @elseif($section === 'claims')
                @php
                    $adminClaimsQuery = fn (array $overrides = []) => array_filter(array_merge([
                        'section' => 'claims',
                        'claim_status' => $claimFilter !== 'all' ? $claimFilter : null,
                        'review_status' => $reviewStatus !== 'all' ? $reviewStatus : null,
                        'category' => $category !== 'all' ? $category : null,
                        'search' => $search ?: null,
                        'sort' => $sort,
                    ], $overrides), fn ($v) => $v !== null && $v !== '');
                @endphp
                <div class="lf-filter-bar lf-admin-claim-filter mb-3">
                    @foreach(['all' => 'All', 'return' => 'Found Reports', 'claim' => 'Claims'] as $type => $label)
                        @php $active = $claimFilter === $type; @endphp
                        <a href="{{ route('admin.dashboard', $adminClaimsQuery(['claim_status' => $type !== 'all' ? $type : null])) }}"
                           class="lf-filter-pill lf-filter-{{ $type }} {{ $active ? 'active' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3 py-2">Type</th>
                                    <th>Status</th>
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
                                            <span class="badge rounded-pill {{ $claim['status'] === 'approved' ? 'bg-success' : ($claim['status'] === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                {{ $claim['status_label'] }}
                                            </span>
                                            @if($claim['dispute_status'] === 'open')
                                                <span class="badge rounded-pill bg-danger">Disputed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $claim['item']['title'] ?? '-' }}</div>
                                            <small class="text-muted"><i class="bi bi-geo-alt-fill"></i> {{ $claim['item']['location'] ?? '' }}</small>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="badge bg-light text-dark border">{{ $claim['item']['category_label'] ?? 'Other' }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $claim['claimant_name'] }}</div>
                                            <small>{{ $claim['contact_info'] }}</small>
                                        </td>
                                        <td class="d-none d-lg-table-cell text-muted">{{ \Illuminate\Support\Str::limit($claim['message'] ?: '-', 40) }}</td>
                                        <td class="text-end pe-3">
                                            @if(!empty($claim['item']))
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1"
                                                        data-bs-toggle="modal" data-bs-target="#admin-claim-item-{{ $claim['id'] }}">
                                                    View
                                                </button>
                                            @endif
                                            @if($claim['status'] === 'pending')
                                                <form method="post" action="{{ route('admin.claims.review', $claim['id']) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill me-1">Approve</button>
                                                </form>
                                                <form method="post" action="{{ route('admin.claims.review', $claim['id']) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill me-1">Reject</button>
                                                </form>
                                            @endif
                                            @if($claim['dispute_status'] === 'open')
                                                <form method="post" action="{{ route('admin.claims.dispute', $claim['id']) }}" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="dispute_status" value="resolved">
                                                    <input type="hidden" name="status" value="pending">
                                                    <button type="submit" class="btn btn-sm btn-warning rounded-pill me-1">Reopen</button>
                                                </form>
                                                <form method="post" action="{{ route('admin.claims.dispute', $claim['id']) }}" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="dispute_status" value="dismissed">
                                                    <input type="hidden" name="status" value="{{ $claim['status'] }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill me-1">Dismiss</button>
                                                </form>
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
                                        <td colspan="7" class="text-center py-4 text-muted">No claims yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-3">{{ $claims->withQueryString()->links() }}</div>
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
                                            @if(($item['moderation_status'] ?? 'active') === 'hidden')
                                                <span class="badge rounded-pill bg-dark">HIDDEN</span>
                                            @endif
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
                                            @if(($item['moderation_status'] ?? 'active') === 'active')
                                                <form method="post" action="{{ route('admin.items.moderate', $item['id']) }}" class="d-inline"
                                                      onsubmit="this.querySelector('[name=reason]').value = prompt('Reason for hiding this report:') || ''; return this.querySelector('[name=reason]').value !== '';">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="moderation_status" value="hidden">
                                                    <input type="hidden" name="reason" value="">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill">Hide</button>
                                                </form>
                                            @else
                                                <form method="post" action="{{ route('admin.items.moderate', $item['id']) }}" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="moderation_status" value="active">
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill">Restore</button>
                                                </form>
                                            @endif
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
                <div class="mt-3">{{ $items->withQueryString()->links() }}</div>
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
                            <p class="text-muted small fw-bold mb-2"><i class="bi bi-geo-alt-fill"></i> {{ $item['location'] }}</p>
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
                                <p class="small fw-bold mb-1">Review status: {{ $claim['status_label'] }}</p>
                                <p class="small fw-bold mb-1">Private ownership proof</p>
                                <p class="bg-warning-subtle p-2 rounded-3 border small mb-2">{{ $claim['ownership_proof'] ?: 'No proof provided.' }}</p>
                                @if(!empty($claim['proof_image_url']))
                                    <a href="{{ $claim['proof_image_url'] }}" target="_blank" rel="noopener">
                                        <img src="{{ $claim['proof_image_url'] }}" alt="Claim proof" class="img-fluid rounded-3 border mb-2">
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    @endif

    <script src="/assets/bootstrap-5.3.3/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-auto-dismiss]').forEach(function (message) {
            window.setTimeout(function () {
                message.style.transition = 'opacity 0.3s ease';
                message.style.opacity = '0';
                window.setTimeout(function () {
                    message.remove();
                }, 300);
            }, 5000);
        });

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
