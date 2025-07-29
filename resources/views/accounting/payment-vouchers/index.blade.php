@extends('layouts.main')

@section('title', 'Payment Vouchers')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">PAYMENT VOUCHERS</h6>
                    <p class="text-muted mb-0">Manage payment vouchers</p>
                </div>
                <div>
                    <a href="{{ route('accounting.payment-vouchers.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i>Create Payment Voucher
                    </a>
                </div>
            </div>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Vouchers</p>
                                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-receipt font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Approved</p>
                                <h4 class="mb-0">{{ $stats['approved'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-check font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Pending</p>
                                <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-time font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Amount</p>
                                <h4 class="mb-0">{{ number_format($stats['total_amount'], 2) }}</h4>
                            </div>
                            <div class="ms-3">
                                <div class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-money font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card radius-10 mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('accounting.payment-vouchers') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" 
                                value="{{ request('search') }}" placeholder="Reference, number, description...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" 
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" 
                                value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('accounting.payment-vouchers') }}" class="btn btn-secondary">
                                <i class="bx bx-refresh me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payment Vouchers List -->
            <div class="card radius-10">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered dt-responsive nowrap" id="paymentVouchersTable">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Bank Account</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $payment->reference }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $payment->reference_type }} - {{ $payment->reference_number }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $payment->formatted_date }}</td>
                                        <td>
                                            <strong class="text-primary">{{ $payment->formatted_amount }}</strong>
                                        </td>
                                        <td>{{ $payment->bankAccount->name ?? 'N/A' }}</td>
                                        <td>{{ $payment->customer->name ?? 'N/A' }}</td>
                                        <td>{!! $payment->status_badge !!}</td>
                                        <td>{{ $payment->user->name }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('accounting.payment-vouchers.show', $payment) }}"
                                                    class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                @if(!$payment->approved)
                                                    <a href="{{ route('accounting.payment-vouchers.edit', $payment) }}"
                                                        class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger delete-payment-btn"
                                                        title="Delete" data-payment-id="{{ $payment->id }}"
                                                        data-payment-reference="{{ $payment->reference }}">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bx bx-receipt font-size-48 mb-3"></i>
                                                <p>No payment vouchers found</p>
                                                <a href="{{ route('accounting.payment-vouchers.create') }}" class="btn btn-primary">
                                                    Create First Payment Voucher
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($payments->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#paymentVouchersTable').DataTable({
                responsive: true,
                order: [[1, 'desc']],
                pageLength: 10,
                language: {
                    search: "",
                    searchPlaceholder: "Search payment vouchers..."
                },
                columnDefs: [
                    { targets: -1, responsivePriority: 1, orderable: false, searchable: false },
                    { targets: [0, 1, 2], responsivePriority: 2 }
                ]
            });

            // SweetAlert delete confirmation
            $('.delete-payment-btn').on('click', function () {
                const paymentId = $(this).data('payment-id');
                const paymentReference = $(this).data('payment-reference');

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete payment voucher "${paymentReference}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create and submit delete form
                        const form = $('<form>', {
                            method: 'POST',
                            action: `/accounting/payment-vouchers/${paymentId}`,
                            style: 'display: none;'
                        });

                        form.append($('<input>', {
                            type: 'hidden',
                            name: '_token',
                            value: '{{ csrf_token() }}'
                        }));

                        form.append($('<input>', {
                            type: 'hidden',
                            name: '_method',
                            value: 'DELETE'
                        }));

                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush 