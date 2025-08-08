@extends('layouts.main')

@section('title', 'Payment Vouchers')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <!-- Breadcrumb -->
            <div class="row">
                <div class="col-12">
                    <div class="page-breadcrumb d-flex align-items-center">
                        <div class="me-auto">
                            <x-breadcrumbs-with-icons :links="[
                                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                                ['label' => 'Payment Vouchers', 'url' => '#', 'icon' => 'bx bx-receipt']
                            ]" />
                        </div>
                        <div class="ms-auto">
                            <a href="{{ route('accounting.payment-vouchers.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus"></i> New Payment Voucher
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <h6 class="mb-0 text-uppercase">PAYMENT VOUCHERS</h6>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4 mb-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Payments</p>
                                <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center">
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
                                <table class="table table-bordered dt-responsive nowrap w-100" id="paymentVouchersTable">
                                    <thead>
                                        <tr>
                                            <th width="10%">Date</th>
                                            <th width="15%">Reference</th>
                                            <th width="15%">Bank Account</th>
                                            <th width="15%">Customer</th>
                                            <th width="15%">Description</th>
                                            <th width="10%">Amount</th>
                                            <th width="10%">Created By</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($paymentVouchers as $payment)
                                            <tr>
                                                <td>{{ $payment->formatted_date }}</td>
                                                <td>
                                                    <a href="{{ route('accounting.payment-vouchers.show', $payment->hash_id) }}"
                                                        class="text-primary fw-bold">
                                                        {{ $payment->reference }}
                                                    </a>
                                                </td>
                                                <td>{{ $payment->bankAccount->name ?? 'N/A' }}</td>
                                                <td>
                                                    @if($payment->customer)
                                                        {{ $payment->customer->name ?? 'N/A' }}
                                                    @else
                                                        <span class="text-muted">No customer</span>
                                                    @endif
                                                </td>
                                                <td>{{ Str::limit($payment->description, 50) ?: 'No description' }}</td>
                                                <td class="text-end fw-bold">{{ $payment->formatted_amount }}</td>
                                                <td>{!! $payment->status_badge !!}</td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('accounting.payment-vouchers.show', $payment->hash_id) }}"
                                                            class="btn btn-sm btn-info">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="{{ route('accounting.payment-vouchers.edit', $payment->hash_id) }}"
                                                            class="btn btn-sm btn-primary">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="deletePaymentVoucher('{{ $payment->hash_id }}', '{{ $payment->reference }}')">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="bx bx-receipt font-size-48 mb-3"></i>
                                                    <h6>No payment vouchers found</h6>
                                                    <p class="mb-0">Create your first payment voucher to get started.</p>
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
            // Initialize DataTable
            $('#paymentVouchersTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                order: [[0, 'desc']], // Sort by date descending by default
                columnDefs: [
                    {
                        targets: -1, // Actions column
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 5, // Amount column
                        className: 'text-end'
                    }
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    emptyTable: "No payment vouchers found"
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function () {
                    // Add custom styling
                    $('.dataTables_wrapper').addClass('mt-3');
                }
            });

            // Delete payment voucher functionality with SweetAlert
            function deletePaymentVoucher(hashId, reference) {
                Swal.fire({
                    title: 'Delete Payment Voucher',
                    text: `Are you sure you want to delete payment voucher "${reference}"? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create and submit form
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': `/accounting/payment-vouchers/${hashId}`
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
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .dataTables_wrapper {
            margin-top: 1rem;
        }

        .dataTables_length select {
            min-width: 80px;
        }

        .dataTables_filter input {
            min-width: 200px;
        }

        .table th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .d-flex.gap-1>* {
            margin-right: 0.25rem;
        }

        .d-flex.gap-1>*:last-child {
            margin-right: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dataTables_filter input {
                min-width: 150px;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }
        }

        /* DataTable pagination styling */
        .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin-left: 2px;
            border: 1px solid #dee2e6;
            background-color: #fff;
            color: #495057;
            cursor: pointer;
        }

        .dataTables_paginate .paginate_button:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
        }

        .dataTables_paginate .paginate_button.current {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .dataTables_paginate .paginate_button.disabled {
            color: #6c757d;
            cursor: not-allowed;
            background-color: #fff;
            border-color: #dee2e6;
        }
    </style>
@endpush 