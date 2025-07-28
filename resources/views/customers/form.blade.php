@php
    $isEdit = isset($customer);
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

<form action="{{ $isEdit ? route('customers.update', $customer) : route('customers.store') }}" 
      method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Name -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name', $customer->name ?? '') }}" placeholder="Enter full name">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <div class="mb-3">
                <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                <select name="sex" id="sex" class="form-control @error('sex') is-invalid @enderror" required>
                    <option value="">-- Select Sex --</option>
                    <option value="M" {{ old('sex', $customer->sex ?? '') == 'M' ? 'selected' : '' }}>Male</option>
                    <option value="F" {{ old('sex', $customer->sex ?? '') == 'F' ? 'selected' : '' }}>Female</option>
                </select>
                @error('sex')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Phone -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
            <input type="text" name="phone1" class="form-control @error('phone1') is-invalid @enderror" 
                   value="{{ old('phone1', $customer->phone1 ?? '') }}" placeholder="Enter phone number">
            @error('phone1') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Region -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Region <!--<span class="text-danger">*</span>--></label>
            <select name="region_id" id="region" class="form-select @error('region_id') is-invalid @enderror">
                <option value="">Select Region</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" {{ old('region_id', $customer->region_id ?? '') == $region->id ? 'selected' : '' }}>
                        {{ $region->name }}
                    </option>
                @endforeach
            </select>
            @error('region_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- District -->
        <div class="col-md-6 mb-3">
            <label class="form-label">District <!--<span class="text-danger">*</span>--></label>
            <select name="district_id" id="district" class="form-select @error('district_id') is-invalid @enderror">
                <option value="">Select District</option>
                @if(old('district_id', $customer->district_id ?? false))
                    <option value="{{ old('district_id', $customer->district_id) }}" selected>
                        {{ \App\Models\District::find(old('district_id', $customer->district_id))->name ?? 'Selected' }}
                    </option>
                @endif
            </select>
            @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Work -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Work</label>
            <input type="text" name="work" class="form-control @error('work') is-invalid @enderror" 
                   value="{{ old('work', $customer->work ?? '') }}" placeholder="e.g. Teacher">
            @error('work') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Work Address -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Work Address</label>
            <input type="text" name="workAddress" class="form-control @error('work_address') is-invalid @enderror" 
                   value="{{ old('work_address', $customer->work_address ?? '') }}" placeholder="e.g. ABC School, Dar">
            @error('work_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- ID Type -->
        <div class="col-md-6 mb-3">
            <label class="form-label">ID Type</label>
            <select name="idType" class="form-select @error('id_type') is-invalid @enderror">
                <option value="">Select ID Type</option>
                @foreach(['National ID', 'License', 'Voter Registration', 'Other'] as $type)
                    <option value="{{ $type }}" {{ old('id_type', $customer->id_type ?? '') == $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
            @error('id_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- ID Number -->
        <div class="col-md-6 mb-3">
            <label class="form-label">ID Number</label>
            <input type="text" name="idNumber" class="form-control @error('id_number') is-invalid @enderror" 
                   value="{{ old('id_number', $customer->id_number ?? '') }}">
            @error('id_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Photo Upload -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Photo</label>
            <input type="file" name="photo" accept="image/*" class="form-control" onchange="previewImage(event)">
            @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div id="preview" class="mt-2">
                @if(isset($customer->photo))
                    <img src="{{ asset('storage/'.$customer->photo) }}" width="100">
                @endif
            </div>
        </div>

        <!-- Document Upload -->

        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Upload Document</label>
                <input type="file" name="document" class="form-control @error('document') is-invalid @enderror"
                    accept=".pdf,.doc,.docx,image/*">
                @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror

                @if(isset($customer->document))
                    <div class="mt-2">
                        <a href="{{ asset('storage/' . $customer->document) }}" target="_blank">
                            View Uploaded Document
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- DOB -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" 
                   value="{{ old('dob', $customer->dob ?? '') }}">
            @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" name="has_cash_collateral" id="has_cash_collateral"
                    {{ old('has_cash_collateral', $customer->has_cash_collateral ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_cash_collateral">Has Cash Collateral</label>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">Assign Loan Officer(s)</label>
            @foreach($loanOfficers as $officer)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="loan_officer_ids[]"
                        value="{{ $officer->id }}"
                        {{ in_array($officer->id, old('loan_officer_ids', $customer->loan_officer_ids ?? [])) ? 'checked' : '' }}>
                    <label class="form-check-label">{{ $officer->name }}</label>
                </div>
            @endforeach
        </div>

    </div>

    <hr class="my-4">

    <div class="d-flex justify-content-between">
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Customers
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update Customer' : 'Create Customer' }}
        </button>
    </div>
</form>
