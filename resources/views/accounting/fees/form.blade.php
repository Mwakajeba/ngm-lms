<form action="{{ isset($fee) ? route('accounting.fees.update', $fee) : route('accounting.fees.store') }}" method="POST">
    @csrf
    @if(isset($fee))
        @method('PUT')
    @endif

    <div class="row">
        <!-- Basic Information -->
        <div class="col-12">
            <h5 class="mb-3 text-primary">Basic Information</h5>
        </div>

        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Fee Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                value="{{ old('name', $fee->name ?? '') }}" placeholder="Enter fee name" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="chart_account_id" class="form-label">Chart Account <span class="text-danger">*</span></label>
            <select class="form-select @error('chart_account_id') is-invalid @enderror" id="chart_account_id"
                name="chart_account_id" required>
                <option value="">-- Select Chart Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('chart_account_id', $fee->chart_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->name }} ({{ $account->account_code }})
                    </option>
                @endforeach
            </select>
            @error('chart_account_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Fee Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary">Fee Configuration</h5>
        </div>

        <div class="col-md-6 mb-3">
            <label for="fee_type" class="form-label">Fee Type <span class="text-danger">*</span></label>
            <select class="form-select @error('fee_type') is-invalid @enderror" id="fee_type" name="fee_type" required>
                <option value="">-- Select Fee Type --</option>
                @foreach($feeTypeOptions as $value => $label)
                    <option value="{{ $value }}" {{ old('fee_type', $fee->fee_type ?? 'fixed') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('fee_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount"
                    name="amount" value="{{ old('amount', $fee->amount ?? '') }}" min="0" step="0.01"
                    placeholder="Enter amount" required>
                <span class="input-group-text" id="amount-suffix">TZS</span>
            </div>
            <div class="form-text" id="amount-help">Enter the fee amount</div>
            @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="">-- Select Status --</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $fee->status ?? 'active') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="branch_id" class="form-label">Branch</label>
            <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                <option value="">-- Select Branch (Optional) --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $fee->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Description -->
        <div class="col-12">
            <h5 class="mb-3 text-primary">Additional Information</h5>
        </div>

        <div class="col-12 mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                name="description" rows="3"
                placeholder="Enter fee description">{{ old('description', $fee->description ?? '') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between">
        <a href="{{ route('accounting.fees.index') }}" class="btn btn-secondary">
            Back to Fees
        </a>
        <button type="submit" class="btn btn-primary">
            {{ isset($fee) ? 'Update Fee' : 'Create Fee' }}
        </button>
    </div>
</form>

@push('scripts')
    <script>
        $(document).ready(function () {
            // Update amount suffix and help text based on fee type
            function updateAmountField() {
                const feeType = $('#fee_type').val();
                const suffix = $('#amount-suffix');
                const help = $('#amount-help');

                if (feeType === 'percentage') {
                    suffix.text('%');
                    help.text('Enter the percentage value (e.g., 5 for 5%)');
                } else {
                    suffix.text('TZS');
                    help.text('Enter the fee amount');
                }
            }

            // Update on page load
            updateAmountField();

            // Update when fee type changes
            $('#fee_type').on('change', updateAmountField);
        });
    </script>
@endpush