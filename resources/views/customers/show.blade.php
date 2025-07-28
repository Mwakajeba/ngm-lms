@extends('layouts.main')

@section('title', 'Customer Profile')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <h6 class="mb-0 text-uppercase">CUSTOMER PROFILE</h6>
        <hr/>
        <div class="row">
            <!-- Profile Card -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title bg-soft-primary text-primary rounded-circle font-size-24">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                            </div>
                            <h5 class="font-size-16 mb-1 text-truncate">{{ $customer->name }}</h5>
                            <p class="text-muted text-truncate mb-3">{{ $customer->phone1 ?? 'No phone' }}</p>
                        </div>

                        <hr class="my-4">

                        <div class="text-muted">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <th scope="row">Customer ID :</th>
                                            <td>{{ $customer->customerNo }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Phone :</th>
                                            <td>{{ $customer->phone1 }}</td>
                                        </tr>
                                         <tr>
                                            <th scope="row">Alt Phone :</th>
                                            <td>{{ $customer->phone2 }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Region :</th>
                                            <td>{{ $customer->region->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">District :</th>
                                            <td>{{ $customer->district->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Branch :</th>
                                            <td>{{ $customer->branch->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Company :</th>
                                            <td>{{ $customer->company->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Joined :</th>
                                            <td>{{ $customer->created_at->format('M d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Last Updated :</th>
                                            <td>{{ $customer->updated_at->format('M d, Y') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Cash Collateral Records</h4>
                        <hr class="my-4">

                        <div class="table-responsive">
                            <!-- <table class="table table-bordered dt-responsive nowrap" id="collateralTable">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->collaterals as $collateral)
                                        <tr>
                                            <td>{{ $collateral->type }}</td>
                                            <td>{{ number_format($collateral->value, 2) }}</td>
                                            <td>{{ $collateral->created_at->format('M d, Y') }}</td>
                                            <td>{{ $collateral->status }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('collaterals.edit', $collateral->id) }}" class="btn btn-sm btn-primary">
                                                    Edit
                                                </a>
                                                <form action="{{ route('collaterals.destroy', $collateral->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table> -->

                    </div>
                </div>

                <!-- Roles and Permissions Card -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Loans Records</h4>
                        <hr class="my-4">
                        <div class="table-responsive">
                            <!-- <table class="table table-bordered dt-responsive nowrap" id="loansTable">
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
                            </table> -->

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