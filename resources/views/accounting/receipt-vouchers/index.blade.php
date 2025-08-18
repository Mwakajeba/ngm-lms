@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Receipt Vouchers')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Receipt Vouchers', 'url' => '#', 'icon' => 'bx bx-receipt']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">RECEIPT VOUCHERS</h6>
                    <p class="text-muted mb-0">Manage receipt voucher entries</p>
                </div>
                <div>
                    @can('create receipt voucher')
                    <a href="{{ route('accounting.receipt-vouchers.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus-circle me-2"></i>New Receipt Voucher
                    </a>
                    @endcan
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
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-receipt'></i></div>
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
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-calendar'></i></div>
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
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money'></i></div>
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
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money'></i></div>
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
                                            <th width="15%">Bank Account</th>
                                            <th width="15%">Payee</th>
                                            <th width="15%">Description</th>
                                            <th width="10%">Amount</th>
                                            <th width="10%">Created By</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($receipts as $receipt)
                                            <tr>
                                                <td>{{ $receipt->formatted_date }}</td>
                                                <td>{{ $receipt->bankAccount->name ?? 'N/A' }}</td>
                                                <td>
                                                    @if($receipt->payee_type === 'customer' && $receipt->customer)
                                                        <span class="badge bg-info me-1">Customer</span>
                                                        {{ $receipt->customer->name }}
                                                    @elseif($receipt->payee_type === 'other')
                                                        <span class="badge bg-secondary me-1">Other</span>
                                                        {{ $receipt->payee_name }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>{{ Str::limit($receipt->description, 50) ?: 'No description' }}</td>
                                                <td class="text-end fw-bold">{{ $receipt->formatted_amount }}</td>
                                                <td>{{ $receipt->user->name ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        @can('view receipt voucher details')
                                                        <a href="{{ route('accounting.receipt-vouchers.show', Hashids::encode($receipt->id)) }}"
                                                            class="btn btn-sm btn-outline-success" title="View">
                                                            <i class="bx bx-show"></i> View
                                                        </a>
                                                        @endcan
                                                        @if($receipt->reference_type === 'manual')
                                                            @can('edit receipt voucher')
                                                            <a href="{{ route('accounting.receipt-vouchers.edit', Hashids::encode($receipt->id)) }}"
                                                                class="btn btn-sm btn-outline-info" title="Edit">
                                                                <i class="bx bx-edit"></i> Edit
                                                            </a>
                                                            @endcan
                                                            @can('delete receipt voucher')
                                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                                data-id="{{ Hashids::encode($receipt->id) }}"
                                                                data-reference="{{ $receipt->reference }}" title="Delete">
                                                                <i class="bx bx-trash"></i> Delete
                                                            </button>
                                                            @endcan
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit/Delete locked: Source is {{ ucfirst($receipt->reference_type) }} transaction" disabled>
                                                                <i class="bx bx-lock"></i> Locked
                                                            </button>
                                                        @endif
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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#receiptVouchersTable').DataTable({
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
                    emptyTable: "No receipt vouchers found"
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function () {
                    // Add custom styling
                    $('.dataTables_wrapper').addClass('mt-3');
                }
            });

            // Delete receipt voucher functionality with SweetAlert
            $(document).on('click', '.delete-btn', function () {
                const receiptId = $(this).data('id');
                const receiptReference = $(this).data('reference');

                Swal.fire({
                    title: 'Delete Receipt Voucher',
                    text: `Are you sure you want to delete receipt voucher "${receiptReference}"? This action cannot be undone.`,
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