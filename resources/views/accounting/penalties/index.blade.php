@extends('layouts.main')

@section('title', 'Penalties Management')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <h6 class="mb-0 text-uppercase">PENALTIES MANAGEMENT</h6>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Penalties</p>
                                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-error-circle font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Active Penalties</p>
                                <h4 class="mb-0">{{ $stats['active'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-check-circle font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Fixed Penalties</p>
                                <h4 class="mb-0">{{ $stats['fixed'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-dollar font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Percentage Penalties</p>
                                <h4 class="mb-0">{{ $stats['percentage'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-percentage font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deduction Type Stats -->
            <div class="row row-cols-1 row-cols-lg-2 mb-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Outstanding Amount Deduction</p>
                                <h4 class="mb-0">{{ $stats['outstanding_amount'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-money font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Principal Deduction</p>
                                <h4 class="mb-0">{{ $stats['principal'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-home font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">Penalties List</h4>
                                <div>
                                    <a href="{{ route('accounting.penalties.create') }}" class="btn btn-primary">
                                        Add Penalty
                                    </a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap w-100" id="penaltiesTable">
                                    <thead>
                                        <tr>
                                            <th width="15%">Name</th>
                                            <th width="15%">Chart Account</th>
                                            <th width="10%">Type</th>
                                            <th width="10%">Amount</th>
                                            <th width="15%">Deduction Type</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Company</th>
                                            <th width="10%">Branch</th>
                                            <th width="10%">Created By</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($penalties as $penalty)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-danger text-white rounded-circle me-3">
                                                            <i class="bx bx-error-circle"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">{{ $penalty->name }}</h6>
                                                            @if($penalty->description)
                                                                <small
                                                                    class="text-muted">{{ Str::limit($penalty->description, 50) }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $penalty->chartAccount->account_name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>{!! $penalty->penalty_type_badge !!}</td>
                                                <td>
                                                    <strong>{{ $penalty->formatted_amount }}</strong>
                                                </td>
                                                <td>{!! $penalty->deduction_type_badge !!}</td>
                                                <td>{!! $penalty->status_badge !!}</td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $penalty->company->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ $penalty->branch->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $penalty->createdBy->name ?? 'N/A' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('accounting.penalties.show', $penalty) }}"
                                                            class="btn btn-sm btn-outline-primary" title="View">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="{{ route('accounting.penalties.edit', $penalty) }}"
                                                            class="btn btn-sm btn-outline-warning" title="Edit">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <form action="{{ route('accounting.penalties.destroy', $penalty) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Are you sure you want to delete this penalty?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                title="Delete">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-error-circle font-size-48 mb-3"></i>
                                                        <h5>No Penalties Found</h5>
                                                        <p>Start by creating your first penalty configuration.</p>
                                                        <a href="{{ route('accounting.penalties.create') }}"
                                                            class="btn btn-primary">
                                                            <i class="bx bx-plus me-2"></i>Add First Penalty
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#penaltiesTable').DataTable({
                responsive: true,
                order: [[0, 'asc']],
                pageLength: 25,
                language: {
                    search: "Search penalties:",
                    lengthMenu: "Show _MENU_ penalties per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ penalties",
                    infoEmpty: "Showing 0 to 0 of 0 penalties",
                    infoFiltered: "(filtered from _MAX_ total penalties)",
                    emptyTable: "No penalties available",
                    zeroRecords: "No matching penalties found"
                }
            });
        });
    </script>
@endpush