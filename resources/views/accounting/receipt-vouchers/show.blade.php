@extends('layouts.main')

@section('title', 'Receipt Voucher Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">RECEIPT VOUCHER DETAILS</h6>
                    <p class="text-muted mb-0">View receipt voucher information</p>
                </div>
                <div>
                    <a href="{{ route('accounting.receipt-vouchers.edit', 1) }}" class="btn btn-primary me-2">
                        <i class="bx bx-edit me-2"></i>Edit Receipt Voucher
                    </a>
                    <a href="{{ route('accounting.receipt-vouchers.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-2"></i>Back to Receipt Vouchers
                    </a>
                </div>
            </div>
            <hr />

            <!-- Prominent Header Card -->
            <div class="card radius-10 bg-gradient-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div
                            class="avatar-lg bg-white text-success rounded-circle me-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-receipt font-size-32"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1">Receipt Voucher #RV-001</h3>
                            <p class="mb-0 opacity-75">Sample receipt voucher for demonstration</p>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark">
                                <i class="bx bx-calendar me-1"></i>
                                {{ date('M d, Y') }}
                            </span>
                            <span class="badge bg-light text-dark">
                                <i class="bx bx-dollar me-1"></i>
                                {{ number_format(5000.00, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column - Main Information -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Date</label>
                                    <p class="form-control-plaintext">{{ date('M d, Y') }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Reference</label>
                                    <p class="form-control-plaintext">RV-001</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Bank Account</label>
                                    <p class="form-control-plaintext">Sample Bank - 1234567890</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Payee</label>
                                    <p class="form-control-plaintext">John Doe (CUST-001)</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <p class="form-control-plaintext">Payment received for services rendered</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Line Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="35%">Account</th>
                                            <th width="35%">Description</th>
                                            <th width="30%" class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Cash Account (1001)</td>
                                            <td>Cash payment received</td>
                                            <td class="text-end fw-bold">{{ number_format(3000.00, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Service Revenue (4001)</td>
                                            <td>Service fee payment</td>
                                            <td class="text-end fw-bold">{{ number_format(2000.00, 2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th>Total</th>
                                            <th class="text-end fw-bold text-success">{{ number_format(5000.00, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Sidebar Information -->
                <div class="col-lg-4">
                    <!-- Organization Information -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bx bx-building me-2"></i>Organization</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-building me-2"></i>Company
                                </label>
                                <p class="form-control-plaintext">Sample Company Ltd</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-map-pin me-2"></i>Branch
                                </label>
                                <p class="form-control-plaintext">Main Branch</p>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Information -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bx bx-history me-2"></i>Audit Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-user me-2"></i>Created By
                                </label>
                                <p class="form-control-plaintext">Admin User</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-calendar me-2"></i>Created Date
                                </label>
                                <p class="form-control-plaintext">{{ date('M d, Y H:i A') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-time me-2"></i>Last Updated
                                </label>
                                <p class="form-control-plaintext">{{ date('M d, Y H:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card radius-10">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('accounting.receipt-vouchers.edit', 1) }}" class="btn btn-primary">
                                    <i class="bx bx-edit me-1"></i>Edit
                                </a>
                                <a href="{{ route('accounting.receipt-vouchers.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteReceiptVoucher()">
                                    <i class="bx bx-trash me-1"></i>Delete
                                </button>
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
        function deleteReceiptVoucher() {
            Swal.fire({
                title: 'Delete Receipt Voucher',
                text: 'Are you sure you want to delete this receipt voucher? This action cannot be undone.',
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
                        'action': '{{ route("accounting.receipt-vouchers.destroy", 1) }}'
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
    </script>
@endpush

@push('styles')
    <style>
        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }

        .font-size-32 {
            font-size: 2rem;
        }
    </style>
@endpush