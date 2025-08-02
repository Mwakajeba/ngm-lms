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
        <div class="col-md-6 mb-3">
            <label class="form-label">Customer <span class="text-danger">*</span></label>
            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ old('customer_id', $loan->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                    {{ $customer->name }}
                </option>
                @endforeach
            </select>
            @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Group -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Group</label>
            <select name="group_id" class="form-select @error('group_id') is-invalid @enderror">
                <option value="">Select Group</option>
                @foreach($groups as $group)
                <option value="{{ $group->id }}" {{ old('group_id', $loan->group_id ?? '') == $group->id ? 'selected' : '' }}>
                    {{ $group->name }}
                </option>
                @endforeach
            </select>
            @error('group_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Product -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                <option value="">Select Product</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" {{ old('product_id', $loan->product_id ?? '') == $product->id ? 'selected' : '' }}>
                    {{ $product->name }}
                </option>
                @endforeach
            </select>
            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Amount -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror"
                value="{{ old('amount', $loan->amount ?? '') }}" placeholder="Enter loan amount" required>
            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Period -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Period (months) <span class="text-danger">*</span></label>
            <input type="number" name="period" class="form-control @error('period') is-invalid @enderror"
                value="{{ old('period', $loan->period ?? '') }}" placeholder="Enter period in months" required>
            @error('period') <div class="invalid-feedback">{{ $message }}</div> @enderror
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