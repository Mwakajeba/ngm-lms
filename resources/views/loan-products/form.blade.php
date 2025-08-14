@php
    use Vinkla\Hashids\Facades\Hashids;
    $isEdit = isset($loanProduct);
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

<form action="{{ $isEdit ? route('loan-products.update', Hashids::encode($loanProduct->id)) : route('loan-products.store') }}" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Basic Information -->
        <div class="col-12">
            <h5 class="mb-3 text-primary">Basic Information</h5>
        </div>

        <!-- Name -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Product Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $loanProduct->name ?? '') }}" placeholder="Enter product name">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Product Type -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Product Type <span class="text-danger">*</span></label>
            <select name="product_type" class="form-select @error('product_type') is-invalid @enderror" required>
                <option value="">-- Select Product Type --</option>
                @foreach($productTypes as $key => $value)
                    <option value="{{ $key }}" {{ old('product_type', $loanProduct->product_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('product_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Interest Rate Range -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Minimum Interest Rate (%) <span class="text-danger">*</span></label>
            <input type="number" name="minimum_interest_rate" step="0.01" min="0" max="100"
                class="form-control @error('minimum_interest_rate') is-invalid @enderror"
                value="{{ old('minimum_interest_rate', $loanProduct->minimum_interest_rate ?? '') }}"
                placeholder="0.00">
            @error('minimum_interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Maximum Interest Rate (%) <span class="text-danger">*</span></label>
            <input type="number" name="maximum_interest_rate" step="0.01" min="0" max="100"
                class="form-control @error('maximum_interest_rate') is-invalid @enderror"
                value="{{ old('maximum_interest_rate', $loanProduct->maximum_interest_rate ?? '') }}"
                placeholder="0.00">
            @error('maximum_interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

        <div class="col-md-6 mb-3">
            <label class="form-label">Interest Method <span class="text-danger">*</span></label>
            <select name="interest_method" class="form-select @error('interest_method') is-invalid @enderror" required>
                <option value="">-- Select Interest Method --</option>
                @foreach($interestMethods as $key => $value)
                    <option value="{{ $key }}" {{ old('interest_method', $loanProduct->interest_method ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('interest_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Principal Range -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Minimum Principal <span class="text-danger">*</span></label>
            <input type="number" name="minimum_principal" step="0.01" min="0"
                class="form-control @error('minimum_principal') is-invalid @enderror"
                value="{{ old('minimum_principal', $loanProduct->minimum_principal ?? '') }}" placeholder="0.00">
            @error('minimum_principal') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Maximum Principal <span class="text-danger">*</span></label>
            <input type="number" name="maximum_principal" step="0.01" min="0"
                class="form-control @error('maximum_principal') is-invalid @enderror"
                value="{{ old('maximum_principal', $loanProduct->maximum_principal ?? '') }}" placeholder="0.00">
            @error('maximum_principal') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Period Range -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Minimum Period <span class="text-danger">*</span></label>
            <input type="number" name="minimum_period" min="1"
                class="form-control @error('minimum_period') is-invalid @enderror"
                value="{{ old('minimum_period', $loanProduct->minimum_period ?? '') }}" placeholder="1">
            @error('minimum_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Maximum Period <span class="text-danger">*</span></label>
            <input type="number" name="maximum_period" min="1"
                class="form-control @error('maximum_period') is-invalid @enderror"
                value="{{ old('maximum_period', $loanProduct->maximum_period ?? '') }}" placeholder="12">
            @error('maximum_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Grace Period (Optional) -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Grace Period (days)</label>
            <input type="number" name="grace_period" min="0"
                class="form-control @error('grace_period') is-invalid @enderror"
                value="{{ old('grace_period', $loanProduct->grace_period ?? '') }}" placeholder="0">
            @error('grace_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>


        <div class="col-md-6 mb-3">
            <label class="form-label">Penalty Criteria Deduction <span class="text-danger">*</span></label>
            <select name="penalt_deduction_criteria" id="penalt_deduction_criteria" class="form-select @error('penalt_deduction_criteria') is-invalid @enderror">
                <option value="">-- Select Deduction Type --</option>
                @foreach($penaltycriteriaDeductions as $key => $value)
                    <option value="{{ $key }}" {{ old('penalt_deduction_criteria', $loanProduct->penalt_deduction_criteria ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('penalt_deduction_criteria') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Top Up Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Top Up Configuration</h5>
        </div>

        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_top_up" id="has_top_up" 
                       value="1" {{ old('has_top_up', isset($loanProduct) && $loanProduct->top_up_type != 'none') ? 'checked' : '' }}>
                <label class="form-check-label" for="has_top_up">
                    Has Top Up
                </label>
            </div>
        </div>

        <div class="col-md-6 mb-3" id="top_up_type_div" style="display: none;">
            <label class="form-label">Top Up Type <span class="text-danger">*</span></label>
            <select name="top_up_type" id="top_up_type" class="form-select @error('top_up_type') is-invalid @enderror">
                <option value="">-- Select Top Up Type --</option>
                @foreach($topUpTypes as $key => $value)
                    <option value="{{ $key }}" {{ old('top_up_type', $loanProduct->top_up_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('top_up_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3" id="top_up_value_div" style="display: none;">
            <label class="form-label">Top Up Value <span class="text-danger">*</span></label>
            <input type="number" name="top_up_type_value" step="0.01" min="0" 
                   class="form-control @error('top_up_type_value') is-invalid @enderror"
                   value="{{ old('top_up_type_value', $loanProduct->top_up_type_value ?? '') }}" 
                   placeholder="0.00">
            @error('top_up_type_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Cash Collateral Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Cash Collateral Configuration</h5>
        </div>

        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_cash_collateral" id="has_cash_collateral"
                    value="1" {{ old('has_cash_collateral', $loanProduct->has_cash_collateral ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_cash_collateral">
                    Has Cash Collateral
                </label>
            </div>
        </div>

        <div class="col-md-6 mb-3" id="cash_collateral_type_div" style="display: none;">
            <label class="form-label">Cash Collateral Type</label>
            <select name="cash_collateral_type" class="form-select @error('cash_collateral_type') is-invalid @enderror">
                <option value="">-- Select Cash Collateral Type --</option>
                @foreach($cashCollateralTypes as $collateralType)
                    <option value="{{ $collateralType->name }}" {{ old('cash_collateral_type', $loanProduct->cash_collateral_type ?? '') == $collateralType->name ? 'selected' : '' }}>
                        {{ $collateralType->name }}
                    </option>
                @endforeach
            </select>
            @error('cash_collateral_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3" id="cash_collateral_value_type_div" style="display: none;">
            <label class="form-label">Cash Collateral Value Type</label>
            <select name="cash_collateral_value_type"
                class="form-select @error('cash_collateral_value_type') is-invalid @enderror">
                <option value="">-- Select Value Type --</option>
                @foreach($cashCollateralValueTypes as $key => $value)
                    <option value="{{ $key }}" {{ old('cash_collateral_value_type', $loanProduct->cash_collateral_value_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('cash_collateral_value_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3" id="cash_collateral_value_div" style="display: none;">
            <label class="form-label">Cash Collateral Value</label>
            <input type="number" name="cash_collateral_value" step="0.01" min="0"
                class="form-control @error('cash_collateral_value') is-invalid @enderror"
                value="{{ old('cash_collateral_value', $loanProduct->cash_collateral_value ?? '') }}"
                placeholder="0.00">
            @error('cash_collateral_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Approval Levels Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Approval Configuration</h5>
        </div>

        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_approval_levels" id="has_approval_levels"
                    value="1" {{ old('has_approval_levels', $loanProduct->has_approval_levels ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_approval_levels">
                    Has Approval Levels
                </label>
            </div>
        </div>

        <div class="col-12" id="approval_levels_div" style="display: none;">
            <div class="card border">
                <div class="card-body">
                    <h6 class="card-title mb-3">Approval Levels Configuration</h6>
                    <p class="text-muted small mb-3">Select roles from the left and move them to the right to define the approval hierarchy. The first role selected will be the first to approve (First In, Last Out).</p>
                    
                    <div class="row">
                        <!-- Available Roles (Left) -->
                        <div class="col-md-5">
                            <label class="form-label">Available Roles</label>
                            <select id="available_roles" class="form-select" size="8" multiple>
                                @if(isset($loanProduct) && $loanProduct->approval_levels)
                                    @php
                                        $selectedRoles = explode(',', $loanProduct->approval_levels);
                                        $selectedRoleIds = [];
                                        foreach ($selectedRoles as $roleIdentifier) {
                                            $roleIdentifier = trim($roleIdentifier);
                                            if (is_numeric($roleIdentifier)) {
                                                $selectedRoleIds[] = (int)$roleIdentifier;
                                            } else {
                                                $role = $roles->where('name', $roleIdentifier)->first();
                                                if ($role) {
                                                    $selectedRoleIds[] = $role->id;
                                                }
                                            }
                                        }
                                    @endphp
                                    @foreach($roles as $role)
                                        @if(!in_array($role->id, $selectedRoleIds))
                                            <option value="{{ $role->id }}" data-description="{{ $role->description ?? '' }}">
                                                {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                            </option>
                                        @endif
                                    @endforeach
                                @else
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" data-description="{{ $role->description ?? '' }}">
                                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple roles</small>
                        </div>

                        <!-- Move Buttons -->
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" id="move_right" class="btn btn-sm btn-primary mb-2">
                                <i class="bx bx-right-arrow-alt"></i> Add
                            </button>
                            <button type="button" id="move_left" class="btn btn-sm btn-secondary">
                                <i class="bx bx-left-arrow-alt"></i> Remove
                            </button>
                        </div>

                        <!-- Selected Roles (Right) -->
                        <div class="col-md-5">
                            <label class="form-label">Approval Hierarchy</label>
                            <select id="selected_roles" name="approval_levels" class="form-select @error('approval_levels') is-invalid @enderror" size="8" multiple>
                                @if(isset($loanProduct) && $loanProduct->approval_levels)
                                    @php
                                        $selectedRoles = explode(',', $loanProduct->approval_levels);
                                    @endphp
                                    @foreach($selectedRoles as $roleIdentifier)
                                        @php
                                            $roleIdentifier = trim($roleIdentifier);
                                            // Check if it's a role ID (numeric) or role name (string)
                                            if (is_numeric($roleIdentifier)) {
                                                $role = $roles->where('id', $roleIdentifier)->first();
                                            } else {
                                                $role = $roles->where('name', $roleIdentifier)->first();
                                            }
                                        @endphp
                                        @if($role)
                                            <option value="{{ $role->id }}" data-description="{{ $role->description ?? '' }}">
                                                {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Drag to reorder approval sequence</small>
                        </div>
                    </div>

                    <!-- Role Description -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div id="role_description" class="alert alert-info" style="display: none;">
                                <strong>Role Description:</strong> <span id="description_text"></span>
                            </div>
                        </div>
                    </div>

                    @error('approval_levels') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Chart Accounts Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Chart Accounts Configuration</h5>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Principal Receivable Account <span class="text-danger">*</span></label>
            <select name="principal_receivable_account_id"
                class="form-select @error('principal_receivable_account_id') is-invalid @enderror" required>
                <option value="">-- Select Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('principal_receivable_account_id', $loanProduct->principal_receivable_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('principal_receivable_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Interest Receivable Account <span class="text-danger">*</span></label>
            <select name="interest_receivable_account_id"
                class="form-select @error('interest_receivable_account_id') is-invalid @enderror" required>
                <option value="">-- Select Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('interest_receivable_account_id', $loanProduct->interest_receivable_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('interest_receivable_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Interest Revenue Account <span class="text-danger">*</span></label>
            <select name="interest_revenue_account_id"
                class="form-select @error('interest_revenue_account_id') is-invalid @enderror" required>
                <option value="">-- Select Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('interest_revenue_account_id', $loanProduct->interest_revenue_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('interest_revenue_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Fees and Penalties Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Fees and Penalties Configuration</h5>
        </div>

        <!-- Fees Configuration -->
        <div class="col-12 mb-4">
            <div class="card border">
                <div class="card-body">
                    <h6 class="card-title mb-3">Default Fees</h6>
                    <p class="text-muted small mb-3">Add multiple fees that will be applied to loans using this product.</p>
                    
                    <div id="fees_container">
                        @if(isset($loanProduct) && $loanProduct->fees_ids)
                            @foreach($loanProduct->fees_ids as $index => $feeId)
                                <div class="row fee-row mb-2">
                                    <div class="col-md-10">
                                        <select name="fees_id[]" class="form-select fee-select @error('fees_id') is-invalid @enderror">
                                            <option value="">-- Select Fee --</option>
                                            @foreach($fees as $fee)
                                                <option value="{{ $fee->id }}" {{ $feeId == $fee->id ? 'selected' : '' }}>
                                                    {{ $fee->name }} ({{ $fee->fee_type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger remove-fee">
                                            <i class="bx bx-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="row fee-row mb-2">
                                <div class="col-md-10">
                                    <select name="fees_id[]" class="form-select fee-select @error('fees_id') is-invalid @enderror">
                                        <option value="">-- Select Fee --</option>
                                        @foreach($fees as $fee)
                                            <option value="{{ $fee->id }}">
                                                {{ $fee->name }} ({{ $fee->fee_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-fee" style="display: none;">
                                        <i class="bx bx-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="button" id="add_fee" class="btn btn-sm btn-success">
                                <i class="bx bx-plus"></i> Add Another Fee
                            </button>
                        </div>
                    </div>
                    
                    @error('fees_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Penalties Configuration -->
        <div class="col-12 mb-4">
            <div class="card border">
                <div class="card-body">
                    <h6 class="card-title mb-3">Default Penalties</h6>
                    <p class="text-muted small mb-3">Add multiple penalties that will be applied to loans using this product.</p>
                    
                    <div id="penalties_container">
                        @if(isset($loanProduct) && $loanProduct->penalty_ids)
                            @foreach($loanProduct->penalty_ids as $index => $penaltyId)
                                <div class="row penalty-row mb-2">
                                    <div class="col-md-10">
                                        <select name="penalty_id[]" class="form-select penalty-select @error('penalty_id') is-invalid @enderror">
                                            <option value="">-- Select Penalty --</option>
                                            @foreach($penalties as $penalty)
                                                <option value="{{ $penalty->id }}" {{ $penaltyId == $penalty->id ? 'selected' : '' }}>
                                                    {{ $penalty->name }} ({{ $penalty->penalty_type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger remove-penalty">
                                            <i class="bx bx-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="row penalty-row mb-2">
                                <div class="col-md-10">
                                    <select name="penalty_id[]" class="form-select penalty-select @error('penalty_id') is-invalid @enderror">
                                        <option value="">-- Select Penalty --</option>
                                        @foreach($penalties as $penalty)
                                            <option value="{{ $penalty->id }}">
                                                {{ $penalty->name }} ({{ $penalty->penalty_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-penalty" style="display: none;">
                                        <i class="bx bx-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="button" id="add_penalty" class="btn btn-sm btn-success">
                                <i class="bx bx-plus"></i> Add Another Penalty
                            </button>
                        </div>
                    </div>
                    
                    @error('penalty_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Repayment Order -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Repayment Configuration</h5>
        </div>

        <div class="col-12">
            <div class="card border">
                <div class="card-body">
                    <h6 class="card-title mb-3">Repayment Order Configuration</h6>
                    <p class="text-muted small mb-3">Select repayment components from the left and move them to the right to define the order of payment allocation. The first component will be paid first.</p>
                    
                    <div class="row">
                        <!-- Available Components (Left) -->
                        <div class="col-md-5">
                            <label class="form-label">Available Components</label>
                            <select id="available_repayment_components" class="form-select" size="6" multiple>
                                @if(isset($loanProduct) && $loanProduct->repayment_order)
                                    @php
                                        $selectedComponents = explode(',', $loanProduct->repayment_order);
                                        $selectedComponentArray = array_map('trim', $selectedComponents);
                                    @endphp
                                    @if(!in_array('principal', $selectedComponentArray))
                                        <option value="principal" data-description="Principal amount of the loan">
                                            Principal
                                        </option>
                                    @endif
                                    @if(!in_array('interest', $selectedComponentArray))
                                        <option value="interest" data-description="Interest charges on the loan">
                                            Interest
                                        </option>
                                    @endif
                                    @if(!in_array('fees', $selectedComponentArray))
                                        <option value="fees" data-description="Additional fees and charges">
                                            Fees
                                        </option>
                                    @endif
                                    @if(!in_array('penalties', $selectedComponentArray))
                                        <option value="penalties" data-description="Late payment penalties">
                                            Penalties
                                        </option>
                                    @endif
                                @else
                                    <option value="principal" data-description="Principal amount of the loan">
                                        Principal
                                    </option>
                                    <option value="interest" data-description="Interest charges on the loan">
                                        Interest
                                    </option>
                                    <option value="fees" data-description="Additional fees and charges">
                                        Fees
                                    </option>
                                    <option value="penalties" data-description="Late payment penalties">
                                        Penalties
                                    </option>
                                @endif
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple components</small>
                        </div>

                        <!-- Move Buttons -->
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" id="move_repayment_right" class="btn btn-sm btn-primary mb-2">
                                <i class="bx bx-right-arrow-alt"></i> Add
                            </button>
                            <button type="button" id="move_repayment_left" class="btn btn-sm btn-secondary">
                                <i class="bx bx-left-arrow-alt"></i> Remove
                            </button>
                        </div>

                        <!-- Selected Components (Right) -->
                        <div class="col-md-5">
                            <label class="form-label">Repayment Order</label>
                            <select id="selected_repayment_components" name="repayment_order" class="form-select @error('repayment_order') is-invalid @enderror" size="6" multiple>
                                @if(isset($loanProduct) && $loanProduct->repayment_order)
                                    @php
                                        $selectedComponents = explode(',', $loanProduct->repayment_order);
                                    @endphp
                                    @foreach($selectedComponents as $component)
                                        @php
                                            $component = trim($component);
                                            $componentLabels = [
                                                'principal' => 'Principal',
                                                'interest' => 'Interest',
                                                'fees' => 'Fees',
                                                'penalties' => 'Penalties'
                                            ];
                                            $componentDescriptions = [
                                                'principal' => 'Principal amount of the loan',
                                                'interest' => 'Interest charges on the loan',
                                                'fees' => 'Additional fees and charges',
                                                'penalties' => 'Late payment penalties'
                                            ];
                                        @endphp
                                        @if(isset($componentLabels[$component]))
                                            <option value="{{ $component }}" data-description="{{ $componentDescriptions[$component] }}">
                                                {{ $componentLabels[$component] }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Drag to reorder payment sequence</small>
                        </div>
                    </div>

                    <!-- Component Description -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div id="repayment_component_description" class="alert alert-info" style="display: none;">
                                <strong>Component Description:</strong> <span id="repayment_description_text"></span>
                            </div>
                        </div>
                    </div>

                    @error('repayment_order') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('loan-products.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> {{ $isEdit ? 'Update' : 'Create' }} Loan Product
                </button>
            </div>
        </div>
    </div>
</form>

@push('styles')
    <style>
        .dragging {
            opacity: 0.5;
            background-color: #e3f2fd !important;
        }
        
        #available_roles, #selected_roles, #available_repayment_components, #selected_repayment_components {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        
        #available_roles option, #selected_roles option, #available_repayment_components option, #selected_repayment_components option {
            padding: 8px 12px;
            border-bottom: 1px solid #f8f9fa;
            cursor: pointer;
        }
        
        #available_roles option:hover, #selected_roles option:hover, #available_repayment_components option:hover, #selected_repayment_components option:hover {
            background-color: #f8f9fa;
        }
        
        #selected_roles option, #selected_repayment_components option {
            background-color: #e3f2fd;
        }
        
        .approval-levels-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Top Up Toggle
            $('#has_top_up').change(function() {
                if ($(this).is(':checked')) {
                    $('#top_up_type_div').show();
                } else {
                    $('#top_up_type_div, #top_up_value_div').hide();
                    $('#top_up_type').val('none');
                    $('#top_up_type_value').val('');
                }
            });

            // Top Up Type Toggle
            $('#top_up_type').change(function() {
                var selectedValue = $(this).val();
                if (selectedValue === 'percentage' || selectedValue === 'fixed_amount') {
                    $('#top_up_value_div').show();
                } else {
                    $('#top_up_value_div').hide();
                    $('#top_up_type_value').val('');
                }
            });

            // Cash Collateral Toggle
            $('#has_cash_collateral').change(function() {
                if ($(this).is(':checked')) {
                    $('#cash_collateral_type_div, #cash_collateral_value_type_div, #cash_collateral_value_div').show();
                } else {
                    $('#cash_collateral_type_div, #cash_collateral_value_type_div, #cash_collateral_value_div').hide();
                }
            });

            // Approval Levels Toggle
            $('#has_approval_levels').change(function() {
                if ($(this).is(':checked')) {
                    $('#approval_levels_div').show();
                } else {
                    $('#approval_levels_div').hide();
                }
            });

            // Fees and Penalties Dynamic Add/Remove
            $('#add_fee').click(function() {
                var feesContainer = $('#fees_container');
                var feeRow = `
                    <div class="row fee-row mb-2">
                        <div class="col-md-10">
                            <select name="fees_id[]" class="form-select fee-select @error('fees_id') is-invalid @enderror">
                                <option value="">-- Select Fee --</option>
                                @foreach($fees as $fee)
                                    <option value="{{ $fee->id }}">
                                        {{ $fee->name }} ({{ $fee->fee_type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger remove-fee">
                                <i class="bx bx-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                feesContainer.append(feeRow);
                updateFeeRemoveButtons();
            });

            $('#fees_container').on('click', '.remove-fee', function() {
                $(this).closest('.fee-row').remove();
                updateFeeRemoveButtons();
            });

            $('#add_penalty').click(function() {
                var penaltiesContainer = $('#penalties_container');
                var penaltyRow = `
                    <div class="row penalty-row mb-2">
                        <div class="col-md-10">
                            <select name="penalty_id[]" class="form-select penalty-select @error('penalty_id') is-invalid @enderror">
                                <option value="">-- Select Penalty --</option>
                                @foreach($penalties as $penalty)
                                    <option value="{{ $penalty->id }}">
                                        {{ $penalty->name }} ({{ $penalty->penalty_type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger remove-penalty">
                                <i class="bx bx-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                `;
                penaltiesContainer.append(penaltyRow);
                updatePenaltyRemoveButtons();
            });

            $('#penalties_container').on('click', '.remove-penalty', function() {
                $(this).closest('.penalty-row').remove();
                updatePenaltyRemoveButtons();
            });

            // Approval Levels Dual-List Functionality
            $('#move_right').click(function() {
                $('#available_roles option:selected').each(function() {
                    var option = $(this).clone();
                    $('#selected_roles').append(option);
                    $(this).remove();
                });
                updateApprovalLevelsInput();
            });

            $('#move_left').click(function() {
                $('#selected_roles option:selected').each(function() {
                    var option = $(this).clone();
                    $('#available_roles').append(option);
                    $(this).remove();
                });
                updateApprovalLevelsInput();
            });

            // Repayment Order Dual-List Functionality
            $('#move_repayment_right').click(function() {
                $('#available_repayment_components option:selected').each(function() {
                    var option = $(this).clone();
                    $('#selected_repayment_components').append(option);
                    $(this).remove();
                });
                updateRepaymentOrderInput();
            });

            $('#move_repayment_left').click(function() {
                $('#selected_repayment_components option:selected').each(function() {
                    var option = $(this).clone();
                    $('#available_repayment_components').append(option);
                    $(this).remove();
                });
                updateRepaymentOrderInput();
            });

            // Role Description Display
            $('#available_roles, #selected_roles').change(function() {
                var selectedOption = $(this).find('option:selected');
                if (selectedOption.length > 0) {
                    var description = selectedOption.data('description');
                    if (description) {
                        $('#description_text').text(description);
                        $('#role_description').show();
                    } else {
                        $('#role_description').hide();
                    }
                } else {
                    $('#role_description').hide();
                }
            });

            // Repayment Component Description Display
            $('#available_repayment_components, #selected_repayment_components').change(function() {
                var selectedOption = $(this).find('option:selected');
                if (selectedOption.length > 0) {
                    var description = selectedOption.data('description');
                    if (description) {
                        $('#repayment_description_text').text(description);
                        $('#repayment_component_description').show();
                    } else {
                        $('#repayment_component_description').hide();
                    }
                } else {
                    $('#repayment_component_description').hide();
                }
            });

            // Drag and Drop Reordering for Selected Roles
            $('#selected_roles').on('mousedown', 'option', function(e) {
                if (e.which === 1) { // Left mouse button
                    var $this = $(this);
                    var $select = $('#selected_roles');
                    var startY = e.pageY;
                    var startIndex = $this.index();
                    
                    $this.addClass('dragging');
                    
                    $(document).on('mousemove.drag', function(e) {
                        var currentY = e.pageY;
                        var $options = $select.find('option');
                        var currentIndex = Math.floor((currentY - $select.offset().top) / $this.outerHeight());
                        
                        if (currentIndex >= 0 && currentIndex < $options.length && currentIndex !== startIndex) {
                            if (currentIndex > startIndex) {
                                $this.insertAfter($options.eq(currentIndex));
                            } else {
                                $this.insertBefore($options.eq(currentIndex));
                            }
                            startIndex = currentIndex;
                            updateApprovalLevelsInput();
                        }
                    });
                    
                    $(document).on('mouseup.drag', function() {
                        $this.removeClass('dragging');
                        $(document).off('mousemove.drag mouseup.drag');
                    });
                }
            });

            // Drag and Drop Reordering for Selected Repayment Components
            $('#selected_repayment_components').on('mousedown', 'option', function(e) {
                if (e.which === 1) { // Left mouse button
                    var $this = $(this);
                    var $select = $('#selected_repayment_components');
                    var startY = e.pageY;
                    var startIndex = $this.index();
                    
                    $this.addClass('dragging');
                    
                    $(document).on('mousemove.drag', function(e) {
                        var currentY = e.pageY;
                        var $options = $select.find('option');
                        var currentIndex = Math.floor((currentY - $select.offset().top) / $this.outerHeight());
                        
                        if (currentIndex >= 0 && currentIndex < $options.length && currentIndex !== startIndex) {
                            if (currentIndex > startIndex) {
                                $this.insertAfter($options.eq(currentIndex));
                            } else {
                                $this.insertBefore($options.eq(currentIndex));
                            }
                            startIndex = currentIndex;
                            updateRepaymentOrderInput();
                        }
                    });
                    
                    $(document).on('mouseup.drag', function() {
                        $this.removeClass('dragging');
                        $(document).off('mousemove.drag mouseup.drag');
                    });
                }
            });

            // Update hidden input with selected roles
            function updateApprovalLevelsInput() {
                var selectedRoles = [];
                $('#selected_roles option').each(function() {
                    selectedRoles.push($(this).val());
                });
                
                // Update the hidden input or create one if it doesn't exist
                var $hiddenInput = $('input[name="approval_levels"]');
                if ($hiddenInput.length === 0) {
                    $hiddenInput = $('<input type="hidden" name="approval_levels">');
                    $('#selected_roles').after($hiddenInput);
                }
                $hiddenInput.val(selectedRoles.join(','));
            }

            // Update hidden input with selected repayment order
            function updateRepaymentOrderInput() {
                var selectedComponents = [];
                $('#selected_repayment_components option').each(function() {
                    selectedComponents.push($(this).val());
                });
                
                var $hiddenInput = $('input[name="repayment_order"]');
                if ($hiddenInput.length === 0) {
                    $hiddenInput = $('<input type="hidden" name="repayment_order">');
                    $('#selected_repayment_components').after($hiddenInput);
                }
                $hiddenInput.val(selectedComponents.join(','));
            }

            // Initialize approval levels input
            updateApprovalLevelsInput();
            updateRepaymentOrderInput();

            // Initialize fee and penalty remove buttons
            updateFeeRemoveButtons();
            updatePenaltyRemoveButtons();

            // Trigger change events on page load
            $('#has_top_up').trigger('change');
            $('#top_up_type').trigger('change');
            $('#has_cash_collateral').trigger('change');
            $('#has_approval_levels').trigger('change');

            // Functions to update remove button visibility
            function updateFeeRemoveButtons() {
                var feeRows = $('.fee-row');
                if (feeRows.length > 1) {
                    feeRows.find('.remove-fee').show();
                } else {
                    feeRows.find('.remove-fee').hide();
                }
            }

            function updatePenaltyRemoveButtons() {
                var penaltyRows = $('.penalty-row');
                if (penaltyRows.length > 1) {
                    penaltyRows.find('.remove-penalty').show();
                } else {
                    penaltyRows.find('.remove-penalty').hide();
                }
            }
        });
    </script>
@endpush