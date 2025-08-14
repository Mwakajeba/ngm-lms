@extends('layouts.main')

@section('title', 'Payment Voucher Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <!-- Breadcrumb -->
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Payment Vouchers', 'url' => route('accounting.payment-vouchers.index'), 'icon' => 'bx bx-receipt'],
                ['label' => 'Payment Voucher #' . $paymentVoucher->reference, 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">PAYMENT VOUCHER DETAILS</h6>
                    <p class="text-muted mb-0">View payment voucher information</p>
                </div>
                <div>
                    <a href="{{ route('accounting.payment-vouchers.edit', Hashids::encode($paymentVoucher->id)) }}"
                        class="btn btn-primary me-2">
                        <i class="bx bx-edit me-2"></i>Edit Payment Voucher
                    </a>
                    <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-2"></i>Back to Payment Vouchers
                    </a>
                </div>
            </div>
            <hr />

            <!-- Prominent Header Card -->
            <div class="card radius-10 bg-gradient-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div
                            class="avatar-lg bg-white text-danger rounded-circle me-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-receipt font-size-32"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1">Payment Voucher #{{ $paymentVoucher->reference }}</h3>
                            <p class="mb-0 opacity-75">{{ $paymentVoucher->description ?: 'No description provided' }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            {!! $paymentVoucher->status_badge !!}
                            <span class="badge bg-light text-dark">
                                <i class="bx bx-calendar me-1"></i>
                                {{ $paymentVoucher->formatted_date }}
                            </span>
                            <span class="badge bg-light text-dark">
                                <i class="bx bx-dollar me-1"></i>
                                {{ $paymentVoucher->formatted_amount }}
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
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Date</label>
                                    <p class="form-control-plaintext">{{ $paymentVoucher->formatted_date }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Reference</label>
                                    <p class="form-control-plaintext">{{ $paymentVoucher->reference }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Bank Account</label>
                                    <p class="form-control-plaintext">{{ $paymentVoucher->bankAccount->name ?? 'N/A' }} -
                                        {{ $paymentVoucher->bankAccount->account_number ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Payee Type</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-{{ $paymentVoucher->payee_type == 'customer' ? 'primary' : ($paymentVoucher->payee_type == 'supplier' ? 'success' : 'warning') }}">
                                            {{ ucfirst($paymentVoucher->payee_type ?? 'N/A') }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Payee</label>
                                    <p class="form-control-plaintext">
                                        @if($paymentVoucher->payee_type == 'customer' && $paymentVoucher->customer)
                                            {{ $paymentVoucher->customer->name ?? 'N/A' }}
                                            ({{ $paymentVoucher->customer->customerNo ?? 'N/A' }})
                                        @elseif($paymentVoucher->payee_type == 'supplier' && $paymentVoucher->supplier)
                                            {{ $paymentVoucher->supplier->name ?? 'N/A' }}
                                        @elseif($paymentVoucher->payee_type == 'other')
                                            {{ $paymentVoucher->payee_name ?? 'N/A' }}
                                        @else
                                            <span class="text-muted">No payee selected</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <p class="form-control-plaintext">
                                        {{ $paymentVoucher->description ?: 'No description provided' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-secondary text-white">
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
                                        @forelse($paymentVoucher->paymentItems as $item)
                                            <tr>
                                                <td>{{ $item->chartAccount->account_name ?? 'N/A' }}
                                                    ({{ $item->chartAccount->account_code ?? 'N/A' }})</td>
                                                <td>{{ $item->description ?: 'No description' }}</td>
                                                <td class="text-end">{{ $item->formatted_amount }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No line items found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th>Total</th>
                                            <th></th>
                                            <th class="text-end fw-bold">
                                                {{ number_format($paymentVoucher->total_amount, 2) }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- GL Transactions -->
                    @if($paymentVoucher->glTransactions->count() > 0)
                        <div class="card radius-10 mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="bx bx-book me-2"></i>General Ledger Entries</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="40%">Account</th>
                                                <th width="30%" class="text-end">Debit</th>
                                                <th width="30%" class="text-end">Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalDebitGL = 0;
                                                $totalCreditGL = 0;
                                            @endphp
                                            @foreach($paymentVoucher->glTransactions as $glTransaction)
                                                <tr>
                                                    <td>{{ $glTransaction->chartAccount->account_name ?? 'N/A' }}
                                                        ({{ $glTransaction->chartAccount->account_code ?? 'N/A' }})</td>
                                                    <td class="text-end">
                                                        @if($glTransaction->nature === 'debit')
                                                            @php $totalDebitGL += $glTransaction->amount; @endphp
                                                            {{ number_format($glTransaction->amount, 2) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        @if($glTransaction->nature === 'credit')
                                                            @php $totalCreditGL += $glTransaction->amount; @endphp
                                                            {{ number_format($glTransaction->amount, 2) }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th>Total</th>
                                                <th class="text-end fw-bold">{{ number_format($totalDebitGL, 2) }}</th>
                                                <th class="text-end fw-bold">{{ number_format($totalCreditGL, 2) }}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column - Sidebar Information -->
                <div class="col-lg-4">
                    <!-- Organization Information -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bx bx-building me-2"></i>Organization</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-building me-2"></i>Company
                                </label>
                                <p class="form-control-plaintext">
                                    @if($paymentVoucher->customer && $paymentVoucher->customer->company)
                                        {{ $paymentVoucher->customer->company->name ?? 'N/A' }}
                                    @else
                                        <span class="text-muted">No company information</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-map-pin me-2"></i>Branch
                                </label>
                                <p class="form-control-plaintext">{{ $paymentVoucher->branch->name ?? 'N/A' }}</p>
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
                                <p class="form-control-plaintext">{{ $paymentVoucher->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-calendar me-2"></i>Created Date
                                </label>
                                <p class="form-control-plaintext">
                                    {{ $paymentVoucher->created_at ? $paymentVoucher->created_at->format('M d, Y H:i A') : 'N/A' }}
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-time me-2"></i>Last Updated
                                </label>
                                <p class="form-control-plaintext">
                                    {{ $paymentVoucher->updated_at ? $paymentVoucher->updated_at->format('M d, Y H:i A') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card radius-10">
                        <div class="card-header bg-light text-white">
                            <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                @if($paymentVoucher->reference_type === 'manual')
                                    <a href="{{ route('accounting.payment-vouchers.edit', $paymentVoucher->hash_id) }}"
                                        class="btn btn-primary">
                                        <i class="bx bx-edit me-1"></i>Edit
                                    </a>
                                    <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i>Back
                                    </a>
                                    @if($paymentVoucher->attachment)
                                        <a href="{{ route('accounting.payment-vouchers.download-attachment', $paymentVoucher->hash_id) }}"
                                            class="btn btn-info">
                                            <i class="bx bx-download me-1"></i>Download Attachment
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-outline-danger" onclick="deletePaymentVoucher()">
                                        <i class="bx bx-trash me-1"></i>Delete
                                    </button>
                                @else
                                    <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i>Back
                                    </a>
                                    @if($paymentVoucher->attachment)
                                        <a href="{{ route('accounting.payment-vouchers.download-attachment', $paymentVoucher->hash_id) }}"
                                            class="btn btn-info">
                                            <i class="bx bx-download me-1"></i>Download Attachment
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-outline-secondary" title="Edit/Delete locked: Source is {{ ucfirst($paymentVoucher->reference_type) }} transaction" disabled>
                                        <i class="bx bx-lock"></i> Locked
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Attachment Section -->
                    @if($paymentVoucher->attachment)
                        <div class="card radius-10 mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="bx bx-paperclip me-2"></i>Attachment</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $paymentVoucher->attachment_name }}</h6>
                                        <p class="text-muted mb-0">
                                            <i class="bx bx-file-pdf me-1"></i>
                                            PDF document uploaded with this payment voucher
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('accounting.payment-vouchers.download-attachment', $paymentVoucher->hash_id) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="bx bx-download me-1"></i>Download
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAttachment()">
                                            <i class="bx bx-trash me-1"></i>Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card radius-10 mt-4">
                            <div class="card-header bg-light text-white">
                                <h5 class="mb-0"><i class="bx bx-paperclip me-2"></i>Attachment</h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="py-4">
                                    <i class="bx bx-file-pdf font-size-48 text-muted mb-3"></i>
                                    <h6 class="text-muted">No PDF attachment uploaded</h6>
                                    <p class="text-muted mb-0">You can add a PDF attachment when editing this payment voucher.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function deletePaymentVoucher() {
            Swal.fire({
                title: 'Delete Payment Voucher',
                text: 'Are you sure you want to delete this payment voucher? This action cannot be undone.',
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
                        'action': '{{ route("accounting.payment-vouchers.destroy", $paymentVoucher->hash_id) }}'
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

        function deleteAttachment() {
            Swal.fire({
                title: 'Remove Attachment',
                text: 'Are you sure you want to remove this attachment? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form to submit DELETE request
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': '{{ route("accounting.payment-vouchers.remove-attachment", $paymentVoucher->hash_id) }}'
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
        .bg-gradient-danger {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        }

        .font-size-32 {
            font-size: 2rem;
        }
    </style>
@endpush 