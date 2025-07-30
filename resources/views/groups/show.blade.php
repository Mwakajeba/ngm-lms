@extends('layouts.main')

@section('title', 'Group Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Groups', 'url' => route('groups.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Group Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />
                <div>
                    <a href="{{ route('groups.edit', $group) }}" class="btn btn-primary">
                        <i class="bx bx-edit"></i> Edit Group
                    </a>
                </div>
            </div>

            <!-- Group Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <h2 class="text-primary mb-2">{{ $group->name }}</h2>
                            <p class="text-muted mb-0">Group Information</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Group Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-user text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Loan Officer</h5>
                            <h4 class="text-primary mb-0">{{ $group->loanOfficer->name ?? 'N/A' }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-group text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Current Members</h5>
                            <h4 class="text-success mb-0">{{ $group->current_member_count }}</h4>
                            <small class="text-muted">{{ $group->minimum_members }} - {{ $group->maximum_members }}
                                range</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-crown text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Group Leader</h5>
                            <h4 class="text-warning mb-0">{{ $group->groupLeader->name ?? 'N/A' }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-calendar text-info" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Meeting Schedule</h5>
                            <h4 class="text-info mb-0">
                                @if($group->meeting_day && $group->meeting_time)
                                    {{ ucfirst($group->meeting_day) }}<br>
                                    <small>{{ $group->meeting_time->format('H:i') }}</small>
                                @else
                                    Not Set
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Cards -->
            <div class="row">
                <!-- Group Information Card -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-info-circle me-2"></i>Group Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Group Name</label>
                                    <p class="mb-0 fw-bold">{{ $group->name }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Created Date</label>
                                    <p class="mb-0 fw-bold">{{ $group->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Last Updated</label>
                                    <p class="mb-0 fw-bold">{{ $group->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Officer Information Card -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Loan Officer Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Officer Name</label>
                                    <p class="mb-0 fw-bold">{{ $group->loanOfficer->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Email Address</label>
                                    <p class="mb-0 fw-bold">{{ $group->loanOfficer->email ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Phone Number</label>
                                    <p class="mb-0 fw-bold">{{ $group->loanOfficer->phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loans Information Card -->
            {{-- TODO: Uncomment when Loan model is properly set up
            @if($group->loans->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-credit-card me-2"></i>Associated Loans
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Loan ID</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Created Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group->loans as $loan)
                                        <tr>
                                            <td>
                                                <strong>#{{ $loan->id ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $loan->customer->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($loan->amount ?? 0, 2) }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $loan->status == 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($loan->status ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td>{{ $loan->created_at ? $loan->created_at->format('M d, Y') : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-info-circle me-2"></i>Associated Loans
                            </h5>
                        </div>
                        <div class="card-body text-center py-4">
                            <i class="bx bx-credit-card text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">No Loans Associated</h5>
                            <p class="text-muted">This group doesn't have any associated loans yet.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            --}}

            <!-- Group Members Management Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-group me-2"></i>Group Members
                                ({{ $group->current_member_count }}/{{ $group->maximum_members }})
                            </h5>
                            <div>
                                @if($group->canAcceptMoreMembers())
                                    <a href="{{ route('group-members.create', $group) }}" class="btn btn-light btn-sm">
                                        <i class="bx bx-plus"></i> Add Member
                                    </a>
                                @else
                                    <span class="badge bg-warning text-dark">Group Full</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if($group->members->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Member</th>
                                                <th>Joined Date</th>
                                                <th>Notes</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group->members as $member)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div
                                                                class="avatar-sm bg-light-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                                <i class="bx bx-user font-size-16"></i>
                                                            </div>
                                                            <div>
                                                                <strong>{{ $member->customer->name }}</strong>
                                                                <br>
                                                                <small
                                                                    class="text-muted">{{ $member->customer->phone ?? 'No phone' }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $member->joined_date->format('M d, Y') }}</td>
                                                    <td>
                                                        <small class="text-muted">{{ Str::limit($member->notes, 50) }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="removeMember({{ $group->id }}, {{ $member->id }}, '{{ $member->customer->name }}')"
                                                                title="Remove Member">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-group text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No Members Yet</h5>
                                    <p class="text-muted">This group doesn't have any members yet.</p>
                                    @if($group->canAcceptMoreMembers())
                                        <a href="{{ route('group-members.create', $group) }}" class="btn btn-primary">
                                            <i class="bx bx-plus"></i> Add First Member
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            border-bottom: none;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .badge {
            font-size: 0.75em;
            font-weight: 500;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .fw-bold {
            font-weight: 600 !important;
        }

        .avatar-sm {
            width: 2.5rem;
            height: 2.5rem;
        }

        .bg-light-primary {
            background-color: rgba(13, 110, 253, 0.1) !important;
            color: #0d6efd !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function removeMember(groupId, memberId, memberName) {
            Swal.fire({
                title: 'Remove Member?',
                text: `Are you sure you want to remove "${memberName}" from this group?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/groups/${groupId}/members/${memberId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
@endpush