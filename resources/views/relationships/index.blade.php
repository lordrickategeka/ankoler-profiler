{{-- resources/views/relationships/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Relationship Management Dashboard')

@section('content')
    <div class="container-fluid">
        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Relationship Management</h1>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary" onclick="runDiscovery('all')">
                            <i class="fas fa-search"></i> Run Discovery
                        </button>
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="runDiscovery('personal')">Personal
                                    Relationships</a></li>
                            <li><a class="dropdown-item" href="#" onclick="runDiscovery('cross-org')">Cross-Org
                                    Relationships</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('relationships.export') }}">Export Data</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Personal
                                    Relationships</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['total_personal_relationships']) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Verified
                                    Relationships</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['verified_personal_relationships']) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cross-Org Connections
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['total_cross_org_relationships']) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-sitemap fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Verifications
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ number_format($stats['pending_personal_verifications'] + $stats['pending_cross_org_verifications']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Row --}}
        <div class="row">
            {{-- Pending Verifications --}}
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Pending Verifications</h6>
                        <a href="{{ route('relationships.personal') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        @if ($pendingVerifications['personal']->isNotEmpty())
                            <h6 class="text-gray-800">Personal Relationships</h6>
                            @foreach ($pendingVerifications['personal']->take(5) as $relationship)
                                <div class="d-flex align-items-center border-bottom py-2">
                                    <div class="flex-grow-1">
                                        <div class="small">
                                            <strong>{{ $relationship->personA->given_name }}
                                                {{ $relationship->personA->family_name }}</strong>
                                            ↔
                                            <strong>{{ $relationship->personB->given_name }}
                                                {{ $relationship->personB->family_name }}</strong>
                                        </div>
                                        <div class="text-muted small">
                                            {{ ucfirst(str_replace('_', ' ', $relationship->relationship_type)) }}
                                            ({{ round($relationship->confidence_score * 100) }}% confidence)
                                        </div>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success btn-sm"
                                            onclick="verifyRelationship('personal', {{ $relationship->id }})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm"
                                            onclick="rejectRelationship('personal', {{ $relationship->id }})">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if ($pendingVerifications['cross_org']->isNotEmpty())
                            <h6 class="text-gray-800 mt-3">Cross-Organizational</h6>
                            @foreach ($pendingVerifications['cross_org']->take(3) as $relationship)
                                <div class="d-flex align-items-center border-bottom py-2">
                                    <div class="flex-grow-1">
                                        <div class="small">
                                            <strong>{{ $relationship->person->given_name }}
                                                {{ $relationship->person->family_name }}</strong>
                                        </div>
                                        <div class="text-muted small">
                                            {{ $relationship->primaryAffiliation->role_type }} at
                                            {{ $relationship->primaryAffiliation->Organization->legal_name }}
                                            ↔
                                            {{ $relationship->secondaryAffiliation->role_type }} at
                                            {{ $relationship->secondaryAffiliation->Organization->legal_name }}
                                        </div>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-success btn-sm"
                                            onclick="verifyRelationship('cross-org', {{ $relationship->id }})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        @if ($pendingVerifications['personal']->isEmpty() && $pendingVerifications['cross_org']->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p>No pending verifications</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent Discoveries --}}
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Discoveries (Last 7 Days)</h6>
                    </div>
                    <div class="card-body">
                        @if ($recentDiscoveries['personal']->isNotEmpty() || $recentDiscoveries['cross_org']->isNotEmpty())
                            @foreach ($recentDiscoveries['personal'] as $relationship)
                                <div class="d-flex align-items-center border-bottom py-2">
                                    <div class="flex-grow-1">
                                        <div class="small">
                                            <i class="fas fa-users text-primary"></i>
                                            <strong>{{ $relationship->personA->given_name }}
                                                {{ $relationship->personA->family_name }}</strong>
                                            ↔
                                            <strong>{{ $relationship->personB->given_name }}
                                                {{ $relationship->personB->family_name }}</strong>
                                        </div>
                                        <div class="text-muted small">
                                            {{ ucfirst(str_replace('_', ' ', $relationship->relationship_type)) }}
                                            via {{ ucfirst(str_replace('_', ' ', $relationship->discovery_method)) }}
                                        </div>
                                    </div>
                                    <div class="text-muted small">
                                        {{ $relationship->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach

                            @foreach ($recentDiscoveries['cross_org'] as $relationship)
                                <div class="d-flex align-items-center border-bottom py-2">
                                    <div class="flex-grow-1">
                                        <div class="small">
                                            <i class="fas fa-sitemap text-info"></i>
                                            <strong>{{ $relationship->person->given_name }}
                                                {{ $relationship->person->family_name }}</strong>
                                        </div>
                                        <div class="text-muted small">
                                            {{ $relationship->primaryAffiliation->Organization->legal_name }} ↔
                                            {{ $relationship->secondaryAffiliation->Organization->legal_name }}
                                        </div>
                                    </div>
                                    <div class="text-muted small">
                                        {{ $relationship->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-search fa-3x mb-3"></i>
                                <p>No recent discoveries</p>
                                <button class="btn btn-primary" onclick="runDiscovery('all')">Run Discovery</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('relationships.personal') }}"
                                    class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-users"></i> Manage Personal Relationships
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('relationships.cross-org') }}" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-sitemap"></i> Cross-Org Relationships
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <button class="btn btn-outline-success btn-block" onclick="showCreateRelationshipModal()">
                                    <i class="fas fa-plus"></i> Create Manual Relationship
                                </button>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('relationships.export') }}"
                                    class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-download"></i> Export Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Modal --}}
    <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Processing relationships...</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function runDiscovery(type) {
            $('#loadingModal').modal('show');

            fetch('{{ route('relationships.discover') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        type: type
                    })
                })
                .then(response => response.json())
                .then(data => {
                    $('#loadingModal').modal('hide');

                    if (data.success) {
                        showAlert('success',
                            `Discovery completed! Found ${data.results.personal_relationships || 0} personal relationships and ${data.results.cross_org_relationships || 0} cross-org relationships.`
                            );
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showAlert('danger', 'Discovery failed: ' + data.message);
                    }
                })
                .catch(error => {
                    $('#loadingModal').modal('hide');
                    showAlert('danger', 'An error occurred during discovery.');
                });
        }

        function verifyRelationship(type, id) {
            const url = type === 'personal' ?
                `{{ route('relationships.personal.verify', '') }}/${id}` :
                `{{ route('relationships.cross-org.verify', '') }}/${id}`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert('danger', data.message);
                    }
                });
        }

        function rejectRelationship(type, id) {
            if (!confirm('Are you sure you want to reject this relationship?')) return;

            const url = `{{ route('relationships.personal.reject', '') }}/${id}`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert('danger', data.message);
                    }
                });
        }

        function showAlert(type, message) {
            const alertDiv = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
            $('body').append(alertDiv);

            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    </script>
@endpush
