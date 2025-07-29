@extends('layouts.main')

@section('title', 'Receipt Vouchers')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">RECEIPT VOUCHERS</h6>
                    <p class="text-muted mb-0">Manage receipt voucher entries</p>
                </div>
                <div>
                    <a href="{{ route('accounting.receipt-vouchers.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-2"></i>New Receipt Voucher
                    </a>
                </div>
            </div>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4 mb-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Receipts</p>
                                <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
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
                                <p class="text-muted mb-1">This Month</p>
                                <h4 class="mb-0">{{ $stats['this_month'] ?? 0 }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-calendar font-size-24"></i>
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
                                <h4 class="mb-0">{{ number_format($stats['total_amount'] ?? 0, 2) }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center">
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
                                <p class="text-muted mb-1">This Month Amount</p>
                                <h4 class="mb-0">{{ number_format($stats['this_month_amount'] ?? 0, 2) }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-money font-size-24"></i>
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
                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap w-100" id="receiptVouchersTable">
                                    <thead>
                                        <tr>
                                            <th width="10%">Date</th>
                                            <th width="15%">Reference</th>
                                            <th width="15%">Bank Account</th>
                                            <th width="15%">Payee</th>
                                            <th width="15%">Description</th>
                                            <th width="10%">Amount</th>
                                            <th width="10%">Created By</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($receiptVouchers ?? [] as $receiptVoucher)
                                            <tr>
                                                <td>{{ $receiptVoucher->date ?? 'N/A' }}</td>
                                                <td>{{ $receiptVoucher->reference ?? 'N/A' }}</td>
                                                <td>{{ $receiptVoucher->bankAccount->name ?? 'N/A' }}</td>
                                                <td>{{ $receiptVoucher->customer->name ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                        title="{{ $receiptVoucher->description ?? 'No description' }}">
                                                        {{ $receiptVoucher->description ?? 'No description' }}
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold">
                                                    {{ number_format($receiptVoucher->total_amount ?? 0, 2) }}
                                                </td>
                                                <td>{{ $receiptVoucher->createdBy->name ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('accounting.receipt-vouchers.show', $receiptVoucher->id ?? 1) }}"
                                                            class="btn btn-sm btn-outline-primary" title="View">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="{{ route('accounting.receipt-vouchers.edit', $receiptVoucher->id ?? 1) }}"
                                                            class="btn btn-sm btn-outline-warning" title="Edit">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger delete-receipt-btn"
                                                            title="Delete" data-receipt-id="{{ $receiptVoucher->id ?? 1 }}"
                                                            data-receipt-reference="{{ $receiptVoucher->reference ?? 'N/A' }}">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-receipt font-size-48 mb-3"></i>
                                                        <h5>No Receipt Vouchers Found</h5>
                                                        <p>Start by creating your first receipt voucher entry.</p>
                                                        <a href="{{ route('accounting.receipt-vouchers.create') }}"
                                                            class="btn btn-primary">
                                                            <i class="bx bx-plus me-2"></i>Create First Receipt Voucher
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
            $('#receiptVouchersTable').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    search: "Search receipt vouchers:",
                    lengthMenu: "Show _MENU_ receipt vouchers per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ receipt vouchers",
                    infoEmpty: "Showing 0 to 0 of 0 receipt vouchers",
                    infoFiltered: "(filtered from _MAX_ total receipt vouchers)",
                    emptyTable: "No receipt vouchers available",
                    zeroRecords: "No matching receipt vouchers found"
                }
            });

            // Delete receipt voucher functionality with SweetAlert
            $('.delete-receipt-btn').on('click', function () {
                const receiptId = $(this).data('receipt-id');
                const receiptReference = $(this).data('receipt-reference');

                Swal.fire({
                    title: 'Delete Receipt Voucher',
                    text: `Are you sure you want to delete receipt voucher "${receiptReference}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': `/accounting/receipt-vouchers/${receiptId}`
                        });

                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': '_token',
                            'value': '{{ csrf_token() }}'
                        }));

                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': '_method',
                            'value': 'DELETE'
                        }));

                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush