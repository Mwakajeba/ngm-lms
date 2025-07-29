@extends('layouts.main')

@section('title', 'Payment Voucher Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">PAYMENT VOUCHER DETAILS</h6>
                    <p class="text-muted mb-0">View payment voucher information</p>
                </div>
                <div>
                    @if(!$paymentVoucher->approved)
                        <a href="{{ route('accounting.payment-vouchers.edit', $paymentVoucher) }}" class="btn btn-warning me-2">
                            <i class="bx bx-edit me-1"></i>Edit
                        </a>
                        <form action="{{ route('accounting.payment-vouchers.approve', $paymentVoucher) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success me-2" onclick="return confirm('Are you sure you want to approve this payment voucher?')">
                                <i class="bx bx-check me-1"></i>Approve
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('accounting.payment-vouchers') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Payment Vouchers
                    </a>
                </div>
            </div>
            <hr />

            <div class="row">
                <!-- Payment Voucher Details -->
                <div class="col-lg-8">
                    <div class="card radius-10">
                        <div class="card-header">
                            <h6 class="mb-0">Payment Voucher Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Reference</label>
                                    <p class="mb-0">{{ $paymentVoucher->reference }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Reference Type</label>
                                    <p class="mb-0">{{ ucfirst($paymentVoucher->reference_type) }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Reference Number</label>
                                    <p class="mb-0">{{ $paymentVoucher->reference_number }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Date</label>
                                    <p class="mb-0">{{ $paymentVoucher->formatted_date }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <p class="mb-0">{{ $paymentVoucher->description ?: 'No description provided' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Total Amount</label>
                                    <p class="mb-0 text-primary fw-bold">{{ $paymentVoucher->formatted_amount }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <p class="mb-0">{!! $paymentVoucher->status_badge !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Items -->
                    <div class="card radius-10 mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Payment Items</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Chart Account</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($paymentVoucher->paymentItems as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $item->chartAccount->account_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $item->chartAccount->accountClassGroup->accountClass->name }}
                                                    </small>
                                                </td>
                                                <td>{{ $item->description ?: 'No description' }}</td>
                                                <td class="text-end">
                                                    <strong>{{ number_format($item->amount, 2) }}</strong>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No payment items found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th class="text-end">{{ $paymentVoucher->formatted_amount }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Information -->
                <div class="col-lg-4">
                    <!-- Payment Details -->
                    <div class="card radius-10">
                        <div class="card-header">
                            <h6 class="mb-0">Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Bank Account</label>
                                <p class="mb-0">{{ $paymentVoucher->bankAccount->name ?? 'N/A' }}</p>
                                @if($paymentVoucher->bankAccount)
                                    <small class="text-muted">{{ $paymentVoucher->bankAccount->account_number }}</small>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Customer</label>
                                <p class="mb-0">{{ $paymentVoucher->customer->name ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Branch</label>
                                <p class="mb-0">{{ $paymentVoucher->branch->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Attachment -->
                    @if($paymentVoucher->attachment)
                        <div class="card radius-10 mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">Attachment</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-file font-size-24 me-3"></i>
                                    <div class="flex-grow-1">
                                        <p class="mb-1">{{ $paymentVoucher->attachment_name }}</p>
                                        <a href="{{ route('accounting.payment-vouchers.download-attachment', $paymentVoucher) }}" 
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Audit Information -->
                    <div class="card radius-10 mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Audit Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created By</label>
                                <p class="mb-0">{{ $paymentVoucher->user->name }}</p>
                                <small class="text-muted">{{ $paymentVoucher->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            @if($paymentVoucher->approved)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Approved By</label>
                                    <p class="mb-0">{{ $paymentVoucher->approvedBy->name ?? 'N/A' }}</p>
                                    <small class="text-muted">{{ $paymentVoucher->approved_at ? $paymentVoucher->approved_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label fw-bold">Last Updated</label>
                                <p class="mb-0">{{ $paymentVoucher->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 