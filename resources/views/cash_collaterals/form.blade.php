<form 
    action="{{ isset($collateral) ? route('cash_collaterals.update', $collateral->id) : route('cash_collaterals.store') }}" 
    method="POST"
>
    @csrf
    @if(isset($collateral))
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="customer_id" class="form-label">Customer</label>
            <select name="customer_id" id="customer_id" class="form-select" required>
                <option value="">-- Select Customer --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}"
                        {{ old('customer_id', $collateral->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
            @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="type_id" class="form-label">Collateral Type</label>
            <select name="type_id" id="type_id" class="form-select" required>
                <option value="">-- Select Type --</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}"
                        {{ old('type_id', $collateral->type_id ?? '') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
            @error('type_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('cash_collaterals.index') }}" class="btn btn-secondary">Back</a>
        </div>
        <div class="col-md-6 text-end">
            <button type="submit" class="btn btn-primary">
                {{ isset($collateral) ? 'Update' : 'Create' }}
            </button>
        </div>
    </div>
</form>