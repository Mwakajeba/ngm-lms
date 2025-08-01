@extends('layouts.main')

@section('title', 'Customer Profile')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Customers', 'url' => route('customers.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Customer', 'url' => '#', 'icon' => 'bx bx-user']
        ]" />
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">Customer Profile</h6>
            <a href="#" class="btn btn-sm btn-primary">
                <i class="bx bx-plus"></i> Apply for Loan
            </a>
        </div>
        <div class="row">
            <!-- Total Loans -->
            <div class="col-md-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Loans</p>
                                <h4 class="my-1">100,000</h4>
                                <p class="mb-0 font-13 text-success">
                                    <i class="bx bxs-up-arrow align-middle"></i> Up to date
                                </p>
                            </div>
                            <div class="widgets-icons bg-light-success text-success ms-auto">
                                <i class="bx bxs-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Default Loans -->
            <div class="col-md-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Default Loans</p>
                                <h4 class="my-1">100,000</h4>
                                <p class="mb-0 font-13 text-danger">
                                    <i class="bx bxs-down-arrow align-middle"></i> In arrears
                                </p>
                            </div>
                            <div class="widgets-icons bg-light-danger text-danger ms-auto">
                                <i class="bx bxs-error"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Arrears -->
            <div class="col-md-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Arrears</p>
                                <h4 class="my-1">100,000</h4>
                                <p class="mb-0 font-13 text-warning">
                                    <i class="bx bxs-info-circle align-middle"></i> Needs attention
                                </p>
                            </div>
                            <div class="widgets-icons bg-light-warning text-warning ms-auto">
                                <i class="bx bxs-time-five"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Penalties -->
            <div class="col-md-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Pending Penalties</p>
                                <h4 class="my-1">100,000</h4>
                                <p class="mb-0 font-13 text-danger">
                                    <i class="bx bxs-error-circle align-middle"></i> Unpaid
                                </p>
                            </div>
                            <div class="widgets-icons bg-light-danger text-danger ms-auto">
                                <i class="bx bxs-wallet-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            
            <!-- Profile Card -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="avatar-lg mx-auto mb-4">
                                <img 
                                    src="{{ $customer->photo ? asset('storage/' . $customer->photo) : asset('assets/images/avatars/default.png') }}" 
                                    alt="{{ $customer->name }}" 
                                    class="rounded-circle p-1 bg-primary" 
                                    width="110"
                                />
                            </div>
                            <h5 class="font-size-16 mb-1 text-truncate">{{ $customer->name }}</h5>
                            <p class="text-muted text-truncate mb-3">{{ $customer->phone1 ?? 'No phone' }}</p>
                        </div>

                        <hr class="my-4">

                        <div class="text-muted">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr><th scope="row">Customer ID :</th><td>{{ $customer->customerNo }}</td></tr>
                                        <tr><th scope="row">Phone :</th><td>{{ $customer->phone1 }}</td></tr>
                                        <tr><th scope="row">Gender :</th><td>{{ $customer->sex }}</td></tr>
                                        <tr><th scope="row">Alt Phone :</th><td>{{ $customer->phone2 ?? 'N/A'}}</td></tr>
                                        <tr><th scope="row">Work :</th><td>{{ $customer->work ?? 'N/A'}}</td></tr>
                                        <tr><th scope="row">Work Address :</th><td>{{ $customer->workAddress ?? 'N/A'}}</td></tr>
                                        <tr><th scope="row">Id Type :</th><td>{{ $customer->idType ?? 'N/A'}}</td></tr>
                                        <tr><th scope="row">Id Number :</th><td>{{ $customer->idNumber ?? 'N/A'}}</td></tr>
                                        <tr><th scope="row">Region :</th><td>{{ $customer->region->name ?? 'N/A' }}</td></tr>
                                        <tr><th scope="row">District :</th><td>{{ $customer->district->name ?? 'N/A' }}</td></tr>
                                        <tr><th scope="row">Branch :</th><td>{{ $customer->branch->name ?? 'N/A' }}</td></tr>
                                        <tr><th scope="row">Relation with business :</th><td>{{ $customer->relation ?? 'N/A' }}</td></tr>
                                        <tr><th scope="row">Company :</th><td>{{ $customer->company->name ?? 'N/A' }}</td></tr>
                                        <tr><th scope="row">Description :</th><td>{{ $customer->company->description ?? 'N/A' }}</td></tr>
                                        <tr><th scope="row">Registrar :</th><td>{{ $customer->user->name ?? 'N/A' }}</td></tr>
                                        <tr><th scope="row">Joined :</th><td>{{ $customer->created_at->format('M d, Y') }}</td></tr>
                                        <tr><th scope="row">Last Updated :</th><td>{{ $customer->updated_at->format('M d, Y') }}</td></tr>
                                    </tbody>
                                </table>
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Assigned Loan officers :</th>
                                    </tr>
                                    @foreach($customer->loanOfficers as $officers)
                                    <tr>
                                        <td>{{$officers->name}}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-warning flex-fill">
                                <i class="bx bx-edit"></i> Edit
                            </a>

                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="flex-fill">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Are you sure you want to delete this customer?');">
                                    <i class="bx bx-trash"></i> Delete
                                </button>
                            </form>

                            @if ($customer->filetypes->count())
                                @foreach ($customer->filetypes as $type)
                                    @if ($type->pivot->document_path)
                                        <a href="{{ asset('storage/' . $type->pivot->document_path) }}"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary mb-2">
                                            <i class="bx bx-file"></i> {{ $type->name }}
                                        </a>
                                    @else
                                        <p class="text-muted">No document for {{ $type->name }}</p>
                                    @endif
                                @endforeach
                            @else
                                <p class="text-muted">No documents uploaded</p>
                            @endif
                        </div>

                    </div>
                </div>
            </div>


            <!-- Profile Details -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Cash Collateral Records</h5>
                        <hr class="my-4">

                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap" id="collateralTable">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->collaterals as $collateral)
                                        <tr>
                                            <td>{{ $collateral->type->name ?? 'N/A' }}</td>  {{-- Access type name --}}
                                            <td>{{ number_format($collateral->amount, 2) }}</td> {{-- Assuming 'amount' field --}}
                                            <td>{{ $collateral->created_at->format('M d, Y') }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('cash_collaterals.show', Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-warning">
                                                    View
                                                </a>
                                                <a href="{{ route('cash_collaterals.edit', Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-info">
                                                    Edit
                                                </a>
                                                <form action="{{ route('cash_collaterals.destroy', Hashids::encode($collateral->id)) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>

                                                <a href="{{ route('cash_collaterals.deposit',Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-primary">
                                                    Deposit
                                                </a>

                                                <a href="{{ route('cash_collaterals.withdraw', Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-success">
                                                    Withdraw
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                    </div>
                </div>

                <!-- Roles and Permissions Card -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Loans Records</h5>
                        <hr class="my-4">
                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap" id="loansTable">
                                <thead>
                                    <tr>
                                        <th>Loan ID</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Disbursed On</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->loans as $loan)
                                        <tr>
                                            <td>{{ $loan->id }}</td>
                                            <td>{{ number_format($loan->amount, 2) }}</td>
                                            <td>{{ $loan->status }}</td>
                                            <td>{{ $loan->disbursed_at->format('M d, Y') }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-sm btn-info">View</a>
                                                @if($loan->status == 'Pending')
                                                    <a href="{{ route('loans.edit', $loan->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table> 

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->
<!--start overlay-->
<div class="overlay toggle-icon"></div>
<!--end overlay-->
<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<!--End Back To Top Button-->
<footer class="page-footer">
    <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
</footer>

@endsection

@push('scripts')
<script>
// Password toggle functionality
document.getElementById('toggleCurrentPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('current_password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
});

document.getElementById('toggleNewPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('new_password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('new_password_confirmation');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
});

// Password strength checker
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthSection = document.getElementById('passwordStrengthSection');
    const strengthBar = document.getElementById('passwordStrength');
    const feedback = document.getElementById('passwordFeedback');
    
    if (password.length > 0) {
        strengthSection.style.display = 'block';
        
        let strength = 0;
        let feedbackText = '';
        
        // Check length
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;
        
        // Check for lowercase
        if (/[a-z]/.test(password)) strength += 25;
        
        // Check for uppercase
        if (/[A-Z]/.test(password)) strength += 25;
        
        // Check for numbers
        if (/[0-9]/.test(password)) strength += 25;
        
        // Check for special characters
        if (/[^A-Za-z0-9]/.test(password)) strength += 25;
        
        // Cap at 100%
        strength = Math.min(strength, 100);
        
        // Update progress bar
        strengthBar.style.width = strength + '%';
        
        // Update color and feedback
        if (strength < 25) {
            strengthBar.className = 'progress-bar bg-danger';
            feedbackText = 'Very Weak';
        } else if (strength < 50) {
            strengthBar.className = 'progress-bar bg-warning';
            feedbackText = 'Weak';
        } else if (strength < 75) {
            strengthBar.className = 'progress-bar bg-info';
            feedbackText = 'Good';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            feedbackText = 'Strong';
        }
        
        feedback.textContent = feedbackText;
    } else {
        strengthSection.style.display = 'none';
    }
});

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('new_password_confirmation').value;
    const currentPassword = document.getElementById('current_password').value;
    
    if (newPassword && !currentPassword) {
        e.preventDefault();
        alert('Please enter your current password to change it.');
        return false;
    }
    
    if (newPassword && newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New passwords do not match!');
        return false;
    }
    
    if (newPassword && newPassword.length < 8) {
        e.preventDefault();
        alert('New password must be at least 8 characters long!');
        return false;
    }
});
</script>
@endpush 