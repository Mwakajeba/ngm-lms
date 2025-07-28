<form
    action="{{ isset($chartAccount) ? route('accounting.accounts.update', $chartAccount->id) : route('accounting.accounts.store') }}"
    method="POST">
    @csrf
    @if(isset($chartAccount))
        @method('PUT')
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Select Account Class Group</label>
            <select class="form-select" name="account_class_group_id" required>
                <option value="">-- Choose Account Class Group --</option>
                @foreach($accountClassGroups as $group)
                    <option value="{{ $group->id }}" {{ (old('account_class_group_id') == $group->id || (isset($chartAccount) && $chartAccount->account_class_group_id == $group->id)) ? 'selected' : '' }}>
                        {{ $group->accountClass->name ?? 'N/A' }} - {{ $group->name }}
                    </option>
                @endforeach
            </select>
            @error('account_class_group_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Account Code</label>
            <input type="text" class="form-control" name="account_code"
                value="{{ $chartAccount->account_code ?? old('account_code') }}" required
                placeholder="e.g., 1001, 2001, etc.">
            @error('account_code')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Account Name</label>
        <input type="text" class="form-control" name="account_name"
            value="{{ $chartAccount->account_name ?? old('account_name') }}" required
            placeholder="e.g., Cash, Accounts Receivable, etc.">
        @error('account_name')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_cash_flow" value="1" {{ (old('has_cash_flow') || (isset($chartAccount) && $chartAccount->has_cash_flow)) ? 'checked' : '' }}>
                <label class="form-check-label">
                    Has Cash Flow Impact
                </label>
            </div>
            @error('has_cash_flow')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_equity" value="1" {{ (old('has_equity') || (isset($chartAccount) && $chartAccount->has_equity)) ? 'checked' : '' }}>
                <label class="form-check-label">
                    Has Equity Impact
                </label>
            </div>
            @error('has_equity')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('accounting.accounts') }}" class="btn btn-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-{{ isset($chartAccount) ? 'primary' : 'success' }}">
            {{ isset($chartAccount) ? 'Update Account' : 'Create Account' }}
        </button>
    </div>
</form>