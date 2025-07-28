@extends('layouts.main')

@section('title', 'Fees Management')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">FEES MANAGEMENT</h6>
                    <p class="text-muted mb-0">Manage service fees and charges</p>
                </div>
                <div>
                    <a href="{{ route('accounting.fees.create') }}" class="btn btn-primary">
                        Add Fee
                    </a>
                </div>
            </div>
            <hr />

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-12 col-lg-3 col-xl-3">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="">
                                    <p class="mb-1">Total Fees</p>
                                    <h4 class="mb-0 text-primary">{{ $stats['total'] }}</h4>
                                </div>
                                <div class="ms-auto fs-2 text-primary">
                                    <i class="bx bx-dollar-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-3 col-xl-3">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="">
                                    <p class="mb-1">Active Fees</p>
                                    <h4 class="mb-0 text-success">{{ $stats['active'] }}</h4>
                                </div>
                                <div class="ms-auto fs-2 text-success">
                                    <i class="bx bx-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-3 col-xl-3">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="">
                                    <p class="mb-1">Fixed Fees</p>
                                    <h4 class="mb-0 text-info">{{ $stats['fixed'] }}</h4>
                                </div>
                                <div class="ms-auto fs-2 text-info">
                                    <i class="bx bx-money"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-3 col-xl-3">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="">
                                    <p class="mb-1">Percentage Fees</p>
                                    <h4 class="mb-0 text-warning">{{ $stats['percentage'] }}</h4>
                                </div>
                                <div class="ms-auto fs-2 text-warning">
                                    <i class="bx bx-percentage"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fees Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="fees-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Chart Account</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Company</th>
                                    <th>Branch</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fees as $fee)
                                    <tr>
                                        <td>
                                            <a href="{{ route('accounting.fees.show', $fee) }}" class="text-primary fw-bold">
                                                {{ $fee->name }}
                                            </a>
                                        </td>
                                        <td>{{ $fee->chartAccount->name ?? 'N/A' }}</td>
                                        <td>{!! $fee->fee_type_badge !!}</td>
                                        <td>{{ $fee->formatted_amount }}</td>
                                        <td>{!! $fee->status_badge !!}</td>
                                        <td>{{ $fee->company->name ?? 'N/A' }}</td>
                                        <td>{{ $fee->branch->name ?? 'N/A' }}</td>
                                        <td>{{ $fee->createdBy->name ?? 'N/A' }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('accounting.fees.show', $fee) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                                <a href="{{ route('accounting.fees.edit', $fee) }}"
                                                    class="btn btn-sm btn-outline-warning">
                                                    Edit
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-fee-btn"
                                                    title="Delete" data-fee-id="{{ $fee->id }}"
                                                    data-fee-name="{{ $fee->name }}">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#fees-table').DataTable({
                responsive: true,
                order: [[0, 'asc']],
                pageLength: 25,
                language: {
                    search: "Search fees:",
                    lengthMenu: "Show _MENU_ fees per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ fees",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });

            // Delete fee with SweetAlert confirmation
            $('.delete-fee-btn').on('click', function () {
                const feeId = $(this).data('fee-id');
                const feeName = $(this).data('fee-name');

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete fee "${feeName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': `/accounting/fees/${feeId}`
                        });
                        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                        form.append($('<input>', { 'type': 'hidden', 'name': '_method', 'value': 'DELETE' }));
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush