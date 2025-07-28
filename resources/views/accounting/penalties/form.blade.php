@php
    $isEdit = isset($penalty);
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

<form action="{{ $isEdit ? route('accounting.penalties.update', $penalty) : route('accounting.penalties.store') }}" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Basic Information Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penalty Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $penalty->name ?? '') }}" placeholder="Enter penalty name" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">-- Select Status --</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $penalty->status ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                rows="3" placeholder="Enter penalty description">{{ old('description', $penalty->description ?? '') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Penalty Configuration Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Penalty Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penalty Type <span class="text-danger">*</span></label>
                            <select name="penalty_type" class="form-select @error('penalty_type') is-invalid @enderror"
                                required>
                                <option value="">-- Select Penalty Type --</option>
                                @foreach($penaltyTypeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('penalty_type', $penalty->penalty_type ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('penalty_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount', $penalty->amount ?? '') }}" min="0" step="0.01" 
                                placeholder="Enter penalty amount" required>
                            <div class="form-text">Enter amount (fixed) or percentage value</div>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Deduction Type <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input @error('deduction_type') is-invalid @enderror" 
                                            type="radio" name="deduction_type" id="outstanding_amount" 
                                            value="outstanding_amount" 
                                            {{ old('deduction_type', $penalty->deduction_type ?? 'outstanding_amount') == 'outstanding_amount' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="outstanding_amount">
                                            <i class="bx bx-money me-2 text-warning"></i>
                                            <strong>Outstanding Amount</strong>
                                            <br>
                                            <small class="text-muted">Penalty will be calculated based on the outstanding loan amount</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input @error('deduction_type') is-invalid @enderror" 
                                            type="radio" name="deduction_type" id="principal" 
                                            value="principal" 
                                            {{ old('deduction_type', $penalty->deduction_type ?? 'outstanding_amount') == 'principal' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="principal">
                                            <i class="bx bx-home me-2 text-danger"></i>
                                            <strong>Principal</strong>
                                            <br>
                                            <small class="text-muted">Penalty will be calculated based on the original principal amount</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('deduction_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Account Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-book-open me-2"></i>Chart Account</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chart Account <span class="text-danger">*</span></label>
                            <select name="chart_account_id" class="form-select @error('chart_account_id') is-invalid @enderror" required>
                                <option value="">-- Select Chart Account --</option>
                                @foreach($chartAccounts as $account)
                                    <option value="{{ $account->id }}" 
                                        {{ old('chart_account_id', $penalty->chart_account_id ?? '') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_name }} ({{ $account->account_code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select the chart account where penalty income will be recorded</div>
                            @error('chart_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organization Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bx bx-building me-2"></i>Organization</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(!auth()->user()->company_id)
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company</label>
                                <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                                    <option value="">-- Select Company --</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" 
                                            {{ old('company_id', $penalty->company_id ?? '') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" 
                                        {{ old('branch_id', $penalty->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('accounting.penalties.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-2"></i>{{ $isEdit ? 'Update Penalty' : 'Create Penalty' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // Auto-update amount placeholder based on penalty type
    $('select[name="penalty_type"]').on('change', function() {
        const penaltyType = $(this).val();
        const amountInput = $('input[name="amount"]');
        
        if (penaltyType === 'percentage') {
            amountInput.attr('placeholder', 'Enter percentage (e.g., 5.5 for 5.5%)');
            amountInput.attr('max', '100');
        } else {
            amountInput.attr('placeholder', 'Enter fixed amount');
            amountInput.removeAttr('max');
        }
    });

    // Trigger change event on page load
    $(document).ready(function() {
        $('select[name="penalty_type"]').trigger('change');
    });
</script>
@endpush 