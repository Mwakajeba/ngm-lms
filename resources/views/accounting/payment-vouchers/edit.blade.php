@extends('layouts.main')

@section('title', 'Edit Payment Voucher')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">EDIT PAYMENT VOUCHER</h6>
                    <p class="text-muted mb-0">Update payment voucher information</p>
                </div>
                <div>
                    <a href="{{ route('accounting.payment-vouchers.show', $paymentVoucher) }}" class="btn btn-secondary me-2">
                        <i class="bx bx-show me-1"></i>View Details
                    </a>
                    <a href="{{ route('accounting.payment-vouchers') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Payment Vouchers
                    </a>
                </div>
            </div>
            <hr />

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    Please fix the following errors:
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('accounting.payment-vouchers.update', $paymentVoucher) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-lg-8">
                        <div class="card radius-10">
                            <div class="card-header">
                                <h6 class="mb-0">Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reference <span class="text-danger">*</span></label>
                                        <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror"
                                            value="{{ old('reference', $paymentVoucher->reference) }}" placeholder="Enter reference">
                                        @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reference Type <span class="text-danger">*</span></label>
                                        <select name="reference_type" class="form-select @error('reference_type') is-invalid @enderror">
                                            <option value="">Select Reference Type</option>
                                            <option value="invoice" {{ old('reference_type', $paymentVoucher->reference_type) === 'invoice' ? 'selected' : '' }}>Invoice</option>
                                            <option value="purchase" {{ old('reference_type', $paymentVoucher->reference_type) === 'purchase' ? 'selected' : '' }}>Purchase</option>
                                            <option value="manual" {{ old('reference_type', $paymentVoucher->reference_type) === 'manual' ? 'selected' : '' }}>Manual</option>
                                            <option value="expense" {{ old('reference_type', $paymentVoucher->reference_type) === 'expense' ? 'selected' : '' }}>Expense</option>
                                            <option value="other" {{ old('reference_type', $paymentVoucher->reference_type) === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('reference_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reference Number <span class="text-danger">*</span></label>
                                        <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                                            value="{{ old('reference_number', $paymentVoucher->reference_number) }}" placeholder="Enter reference number">
                                        @error('reference_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                            value="{{ old('date', $paymentVoucher->date->format('Y-m-d')) }}">
                                        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                            rows="3" placeholder="Enter description">{{ old('description', $paymentVoucher->description) }}</textarea>
                                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Items -->
                        <div class="card radius-10 mt-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Payment Items</h6>
                                <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                                    <i class="bx bx-plus me-1"></i>Add Item
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="paymentItems">
                                    <!-- Payment items will be added here dynamically -->
                                </div>
                                <div class="text-end mt-3">
                                    <h5>Total Amount: <span id="totalAmount" class="text-primary">0.00</span></h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="card radius-10">
                            <div class="card-header">
                                <h6 class="mb-0">Payment Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                                    <select name="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror">
                                        <option value="">Select Bank Account</option>
                                        @foreach($bankAccounts as $bankAccount)
                                            <option value="{{ $bankAccount->id }}" {{ old('bank_account_id', $paymentVoucher->bank_account_id) == $bankAccount->id ? 'selected' : '' }}>
                                                {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Customer</label>
                                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer_id', $paymentVoucher->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id', $paymentVoucher->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Attachment</label>
                                    <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                                        accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Allowed: PDF, JPG, JPEG, PNG (Max: 2MB)</small>
                                    @if($paymentVoucher->attachment)
                                        <div class="mt-2">
                                            <small class="text-muted">Current: {{ $paymentVoucher->attachment_name }}</small>
                                        </div>
                                    @endif
                                    @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('accounting.payment-vouchers.show', $paymentVoucher) }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Update Payment Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let itemCounter = 0;
        const existingItems = @json($paymentVoucher->paymentItems);

        $(document).ready(function () {
            // Add existing items
            existingItems.forEach(function(item) {
                addPaymentItem(item);
            });

            // Add item button click
            $('#addItemBtn').on('click', function () {
                addPaymentItem();
            });

            // Remove item button click (delegate to handle dynamically added elements)
            $(document).on('click', '.remove-item-btn', function () {
                $(this).closest('.payment-item').remove();
                calculateTotal();
            });

            // Calculate total when amounts change
            $(document).on('input', '.item-amount', function () {
                calculateTotal();
            });

            // Initial total calculation
            calculateTotal();
        });

        function addPaymentItem(existingItem = null) {
            itemCounter++;
            const itemHtml = `
                <div class="payment-item border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Chart Account <span class="text-danger">*</span></label>
                            <select name="items[${itemCounter}][chart_account_id]" class="form-select item-account" required>
                                <option value="">Select Account</option>
                                @foreach($chartAccounts as $account)
                                    <option value="{{ $account->id }}" ${existingItem && existingItem.chart_account_id == {{ $account->id }} ? 'selected' : ''}>
                                        {{ $account->account_name }} ({{ $account->accountClassGroup->accountClass->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="items[${itemCounter}][amount]" class="form-control item-amount" 
                                step="0.01" min="0.01" required placeholder="0.00" 
                                value="${existingItem ? existingItem.amount : ''}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Description</label>
                            <input type="text" name="items[${itemCounter}][description]" class="form-control" 
                                placeholder="Enter description" value="${existingItem ? existingItem.description : ''}">
                        </div>
                        <div class="col-md-1 mb-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" title="Remove Item">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#paymentItems').append(itemHtml);
        }

        function calculateTotal() {
            let total = 0;
            $('.item-amount').each(function () {
                const amount = parseFloat($(this).val()) || 0;
                total += amount;
            });
            $('#totalAmount').text(total.toFixed(2));
        }
    </script>
@endpush 