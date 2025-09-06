@php
    $isEdit = isset($loanApplication);
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

<form action="{{ $isEdit ? route('loans.application.update', $loanApplication) : route('loans.application.store') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Customer -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Customer <span class="text-danger">*</span></label>
            <select name="customer_id" id="customerSelect" class="form-select @error('customer_id') is-invalid @enderror" required>
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" 
                        data-groups="{{ $customer->groups->pluck('id')->toJson() }}"
                        {{ old('customer_id', $loanApplication->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} - {{ $customer->phone1 ?? 'No phone' }}
                    </option>
                @endforeach
            </select>
            @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Group -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Group</label>
            <select name="group_id" id="groupSelect" class="form-select @error('group_id') is-invalid @enderror">
                <option value="">Select Group</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}" {{ old('group_id', $loanApplication->group_id ?? '') == $group->id ? 'selected' : '' }}>
                        {{ $group->name }}
                    </option>
                @endforeach
            </select>
            @error('group_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Product Select -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Loan Product <span class="text-danger">*</span></label>
            <select id="productSelect" name="product_id" class="form-select @error('product_id') is-invalid @enderror"
                required>
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ old('product_id', $loanApplication->product_id ?? '') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>


        <!-- Date Applied -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Date Applied <span class="text-danger">*</span></label>
            <input type="date" name="date_applied" class="form-control @error('date_applied') is-invalid @enderror"
                value="{{ old('date_applied', $loanApplication->date_applied ?? now()->toDateString()) }}" required>
            @error('date_applied') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Amount -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Loan Amount <span class="text-danger">*</span>
                <small id="amountRangeLabel" class="text-muted ms-2"></small>
            </label>
            <input type="number" id="amountInput" step="0.000000000000001" name="amount"
                class="form-control @error('amount') is-invalid @enderror"
                value="{{ old('amount', $loanApplication->amount ?? '') }}" placeholder="Enter loan amount" required>
            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Period -->
        <div class="col-md-6 mb-3">
            <label class="form-label">
                Loan Period (months) <span class="text-danger">*</span>
                <small id="periodRangeLabel" class="text-muted ms-2"></small>
            </label>
            <input type="number" id="periodInput" name="period"
                class="form-control @error('period') is-invalid @enderror"
                value="{{ old('period', $loanApplication->period ?? '') }}" placeholder="Enter period in months"
                required>
            @error('period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Interest Rate -->
        <div class="col-md-6 mb-3">
            <label class="form-label">
                Interest Rate (%) <span class="text-danger">*</span>
                <small id="interestRangeLabel" class="text-muted ms-2"></small>
            </label>
            <input type="number" id="interestInput" step="0.000000000000001" name="interest"
                class="form-control @error('interest') is-invalid @enderror"
                value="{{ old('interest', $loanApplication->interest ?? '') }}" placeholder="Enter interest rate in %"
                required>
            @error('interest') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Sector -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Business Sector <span class="text-danger">*</span></label>
            <select name="sector" class="form-select @error('sector') is-invalid @enderror" required>
                <option value="">Select Sector</option>
                @foreach($sectors as $sector)
                    <option value="{{ $sector }}" {{ old('sector', $loanApplication->sector ?? '') == $sector ? 'selected' : '' }}>
                        {{ $sector }}
                    </option>
                @endforeach
            </select>
            @error('sector') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <!-- Product Information Display -->
    <div id="productInfo" class="card mb-3" style="display: none;">
        <div class="card-header">
            <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Product Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Min Amount:</strong> <span id="minAmount">-</span>
                </div>
                <div class="col-md-3">
                    <strong>Max Amount:</strong> <span id="maxAmount">-</span>
                </div>
                <div class="col-md-3">
                    <strong>Min Period:</strong> <span id="minPeriod">-</span> months
                </div>
                <div class="col-md-3">
                    <strong>Max Period:</strong> <span id="maxPeriod">-</span> months
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <strong>Min Interest:</strong> <span id="minInterest">-</span>%
                </div>
                <div class="col-md-3">
                    <strong>Max Interest:</strong> <span id="maxInterest">-</span>%
                </div>
                <div class="col-md-6">
                    <strong>Description:</strong> <span id="productDescription">-</span>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update Application' : 'Submit Application' }}
        </button>
    </div>
</form>

<script>
    const products = @json($products);
    const groups = @json($groups);

    document.addEventListener("DOMContentLoaded", function () {
        const customerSelect = document.getElementById("customerSelect");
        const groupSelect = document.getElementById("groupSelect");
        const productSelect = document.getElementById("productSelect");
        const periodInput = document.getElementById("periodInput");
        const interestInput = document.getElementById("interestInput");
        const amountInput = document.getElementById("amountInput");
        const periodRangeLabel = document.getElementById("periodRangeLabel");
        const amountRangeLabel = document.getElementById("amountRangeLabel");
        const interestRangeLabel = document.getElementById("interestRangeLabel");
        const productInfo = document.getElementById("productInfo");

        // Handle customer selection to auto-populate group
        customerSelect.addEventListener("change", function () {
            const selectedOption = this.options[this.selectedIndex];
            const customerGroups = selectedOption.getAttribute('data-groups');
            
            // Reset group selection
            groupSelect.value = '';
            
            if (customerGroups) {
                try {
                    const groupIds = JSON.parse(customerGroups);
                    if (groupIds.length > 0) {
                        // Auto-select the first group if customer has groups
                        groupSelect.value = groupIds[0];
                    }
                } catch (e) {
                    console.error('Error parsing customer groups:', e);
                }
            }
        });

        productSelect.addEventListener("change", function () {
            const selectedId = parseInt(this.value);
            const product = products.find(p => p.id === selectedId);

            if (product) {
                // Show product information
                productInfo.style.display = 'block';
                document.getElementById("minAmount").textContent = product.minimum_principal;
                document.getElementById("maxAmount").textContent = product.maximum_principal;
                document.getElementById("minPeriod").textContent = product.minimum_period;
                document.getElementById("maxPeriod").textContent = product.maximum_period;
                document.getElementById("minInterest").textContent = product.minimum_interest_rate;
                document.getElementById("maxInterest").textContent = product.maximum_interest_rate;
                document.getElementById("productDescription").textContent = product.description || 'No description available';

                // Set period limits
                periodInput.min = product.minimum_period;
                periodInput.max = product.maximum_period;
                periodRangeLabel.innerText = `(min: ${product.minimum_period}, max: ${product.maximum_period})`;

                // Set interest limits
                interestInput.min = product.minimum_interest_rate;
                interestInput.max = product.maximum_interest_rate;
                interestRangeLabel.innerText = `(min: ${product.minimum_interest_rate}%, max: ${product.maximum_interest_rate}%)`;

                // Set amount limits
                amountInput.min = product.minimum_principal;
                amountInput.max = product.maximum_principal;
                amountRangeLabel.innerText = `(min: ${product.minimum_principal}, max: ${product.maximum_principal})`;

            } else {
                // Hide product information
                productInfo.style.display = 'none';

                // Clear limits
                periodInput.removeAttribute('min');
                periodInput.removeAttribute('max');
                interestInput.removeAttribute('min');
                interestInput.removeAttribute('max');
                amountInput.removeAttribute('min');
                amountInput.removeAttribute('max');
                periodRangeLabel.innerText = '';
                interestRangeLabel.innerText = '';
                amountRangeLabel.innerText = '';
            }
        });

        // Trigger change events if values are pre-selected (for edit mode)
        if (customerSelect.value) {
            customerSelect.dispatchEvent(new Event('change'));
        }
        if (productSelect.value) {
            productSelect.dispatchEvent(new Event('change'));
        }
    });
</script>