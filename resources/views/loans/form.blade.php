@php
$isEdit = isset($loan);
@endphp

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

<form action="{{ $isEdit ? route('loans.update', $loan) : route('loans.store') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Customer -->
        <div class="row">
            <!-- Customer -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Customer <span class="text-danger">*</span></label>
                <select name="customer_id" id="customer_id" class="form-select select2-single @error('customer_id') is-invalid @enderror" required>
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', $loan->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} - {{ $customer->phone1 }}
                    </option>
                    @endforeach
                </select>
                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Displayed Group (disabled text input) -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Group</label>
                <input type="text" id="group_name" class="form-control" value="" readonly>
            </div>

            <!-- Hidden Group ID for form submission -->
            <input type="hidden" name="group_id" id="group_id" value="{{ old('group_id', $loan->group_id ?? '') }}">
        </div>
        <!-- Loan Officer -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Loan Officer <span class="text-danger">*</span></label>
            <select name="loan_officer"
                class="form-select  select2-single @error('loan_officer') is-invalid @enderror" required>
                <option value="">-- Select Loan Officer --</option>
                @foreach($loanOfficers as $officer)
                <option value="{{ $officer->id }}" {{ old('loan_officer') == $officer->id ? 'selected' : '' }}>
                    {{ $officer->name }} ({{ $officer->email }})
                </option>
                @endforeach
            </select>
            @error('loan_officer')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Interest Cycle and Method -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Interest Cycle <span class="text-danger">*</span></label>
            <select name="interest_cycle" class="form-select @error('interest_cycle') is-invalid @enderror" required>
                <option value="">-- Select Interest Cycle --</option>
                @foreach($interestCycles as $key => $value)
                <option value="{{ $key }}" {{ old('interest_cycle', $loanProduct->interest_cycle ?? '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
                @endforeach
            </select>
            @error('interest_cycle') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Product Select -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Product</label>
            <select id="productSelect" name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                <option value="">Select Product</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" {{ old('product_id', $loan->product_id ?? '') == $product->id ? 'selected' : '' }}>
                    {{ $product->name }}
                </option>
                @endforeach
            </select>
            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!----account from --->
        <div class="col-md-6 mb-3">
            <label class="form-label">From Account</label>
            <select name="account_id" class="form-select @error('account_id') is-invalid @enderror">
                <option value="">Select Account From</option>
                @foreach($bankAccounts as $bankAccount)
                <option value="{{ $bankAccount->id }}" {{ old('bankAccount_id', $loan->bankAccount_id ?? '') == $bankAccount->id ? 'selected' : '' }}>
                    {{ $bankAccount->name }}
                </option>
                @endforeach
            </select>
            @error('bankAccount_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Date Applied -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Date Applied <span class="text-danger">*</span></label>
            <input
                type="date"
                name="date_applied"
                class="form-control @error('date_applied') is-invalid @enderror"
                value="{{ old('date_applied', $loan->date_applied ?? now()->toDateString()) }}"
                required>
            @error('date_applied') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>


        <!-- Amount -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Amount <span class="text-danger">*</span>
                <small id="amountRangeLabel" class="text-muted ms-2"></small>
            </label>
            <input type="number" id="amountInput" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror"
                value="{{ old('amount', $loan->amount ?? '') }}" placeholder="Enter loan amount" required>
            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Period -->
        <div class="col-md-6 mb-3">
            <label class="form-label">
                Period (months) <span class="text-danger">*</span>
                <small id="periodRangeLabel" class="text-muted ms-2"></small>
            </label>
            <input type="number" id="periodInput" name="period" class="form-control @error('period') is-invalid @enderror"
                value="{{ old('period', $loan->period ?? '') }}" placeholder="Enter period in months" required>
            @error('period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Interest Rate -->
        <div class="col-md-6 mb-3">
            <label class="form-label">
                Interest Rate (%) <span class="text-danger">*</span>
                <small id="interestRangeLabel" class="text-muted ms-2"></small>
            </label>
            <input type="number" id="interestInput" step="0.01" name="interest" class="form-control @error('interest') is-invalid @enderror"
                value="{{ old('interest', $loan->interest ?? '') }}" placeholder="Enter interest in %" required>
            @error('interest') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Sector -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Sector</label>
            <select name="sector" class="form-select @error('sector') is-invalid @enderror">
                <option value="">Select Sector</option>
                @foreach($sectors as $sector)
                <option value="{{ $sector }}" {{ old('sector', $loan->sector ?? '') == $sector ? 'selected' : '' }}>
                    {{ $sector }}
                </option>
                @endforeach
            </select>
            @error('sector') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <!-- Add other loan fields as needed -->
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('loans.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Loans
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update Loan' : 'Create Loan' }}
        </button>
    </div>
</form>
<script>
    const products = @json($products);

    document.addEventListener("DOMContentLoaded", function() {
        const productSelect = document.getElementById("productSelect");
        const periodInput = document.getElementById("periodInput");
        const interestInput = document.getElementById("interestInput");
        const amountInput = document.getElementById("amountInput");
        const periodRangeLabel = document.getElementById("periodRangeLabel");
        const amountRangeLabel = document.getElementById("amountRangeLabel");
        const interestRangeLabel = document.getElementById("interestRangeLabel");

        productSelect.addEventListener("change", function() {
            const selectedId = parseInt(this.value);
            const product = products.find(p => p.id === selectedId);

            if (product) {
                // Set period limits
                periodInput.min = product.minimum_period;
                periodInput.max = product.maximum_period;
                periodRangeLabel.innerText = `(min: ${product.minimum_period}, max: ${product.maximum_period})`;

                // Set interest limits
                interestInput.min = product.minimum_interest_rate;
                interestInput.max = product.maximum_interest_rate;
                interestRangeLabel.innerText = `(min: ${product.minimum_interest_rate}%, max: ${product.maximum_interest_rate}%)`;

                ///set amount principal limit

                amountInput.min = product.minimum_principal;
                amountInput.max = product.maximum_principal;
                amountRangeLabel.innerText = `(min: ${product.minimum_principal}%, max: ${product.maximum_principal}%)`;

            } else {
                periodInput.removeAttribute('min');
                periodInput.removeAttribute('max');
                interestInput.removeAttribute('min');
                interestInput.removeAttribute('max');
                periodRangeLabel.innerText = '';
                interestRangeLabel.innerText = '';
            }
        });

        // Initialize Select2 for all .select2-single selects
        if (window.jQuery) {
            $('.select2-single').select2({
                placeholder: 'Select Customer',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });
        }

        // Group update logic for customer select
        const customers = @json($customers);
        const groupIdInput = document.getElementById('group_id');
        const groupNameDisplay = document.getElementById('group_name');

        function updateGroupForCustomer(customerId) {
            const selectedCustomer = customers.find(c => c.id == customerId);
            groupIdInput.value = '';
            groupNameDisplay.value = '';
            if (selectedCustomer && selectedCustomer.groups.length > 0) {
                const group = selectedCustomer.groups[0];
                groupIdInput.value = group.id;
                groupNameDisplay.value = group.name;
            }
        }
        if (window.jQuery) {
            $('#customer_id').on('change', function() {
                updateGroupForCustomer(this.value);
            });
            // Trigger on page load (for edit form or old values)
            $('#customer_id').trigger('change');
        } else {
            const customerSelect = document.getElementById('customer_id');
            customerSelect.addEventListener('change', function() {
                updateGroupForCustomer(this.value);
            });
            // Trigger on page load
            if (customerSelect.value) {
                updateGroupForCustomer(customerSelect.value);
            }
        }
    });
</script>