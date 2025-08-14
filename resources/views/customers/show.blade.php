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
            @can('create loan')
            <a href="#" class="btn btn-sm btn-primary">
                <i class="bx bx-plus"></i> Apply for Loan
            </a>
            @endcan
        </div>
        <div class="row">
            <!-- Total Loans -->
            <div class="col-md-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Loans</p>
                                <h4 class="my-1">
                                    {{ number_format($customer->loans->sum('amount'), 2) }}
                                </h4>
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
                                <h4 class="my-1">
                                    {{
                                        number_format(
                                            \App\Models\LoanSchedule::where('customer_id', $customer->id)
                                                ->whereDate('due_date', '<', now())
                                                ->sum(\DB::raw('principal + interest'))
                                            -
                                            \App\Models\Repayment::where('customer_id', $customer->id)
                                                ->sum(\DB::raw('principal + interest'))
                                        , 2)
                                    }}
                                </h4>
                                <p class="mb-0 font-13 text-danger">
                                    <i class="bx bxs-down-arrow align-middle"></i> In arrears (10 days)
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
                                <h4 class="my-1">
                                    {{
                                        number_format(
                                            \App\Models\LoanSchedule::where('customer_id', $customer->id)
                                                ->whereDate('due_date', '<', now())
                                                ->sum(\DB::raw('principal + interest'))
                                            -
                                            \App\Models\Repayment::where('customer_id', $customer->id)
                                                ->sum(\DB::raw('principal + interest'))
                                        , 2)
                                    }}
                                </h4>
                                <p class="mb-0 font-13 text-warning">
                                    <i class="bx bxs-info-circle align-middle"></i>
                                    @php
                                    $today = now()->toDateString();
                                    $daysInArrears = \DB::table('loan_schedules as s')
                                    ->leftJoin('repayments as r', 's.id', '=', 'r.loan_schedule_id')
                                    ->where('s.customer_id', $customer->id)
                                    ->selectRaw('
                                    s.id,
                                    s.due_date,
                                    (s.principal + s.interest) as amount_due,
                                    IFNULL(SUM(r.principal + r.interest), 0) as total_paid,
                                    CASE
                                    WHEN SUM(r.principal + r.interest) < (s.principal + s.interest)
                                        AND ?> s.due_date
                                        THEN DATEDIFF(?, s.due_date)
                                        WHEN SUM(r.principal + r.interest) >= (s.principal + s.interest)
                                        AND MAX(r.payment_date) > s.due_date
                                        THEN DATEDIFF(MAX(r.payment_date), s.due_date)
                                        ELSE 0
                                        END as days_in_arrears', [$today, $today])
                                        ->groupBy('s.id', 's.due_date', 's.principal', 's.interest')
                                        ->orderBy('s.due_date')
                                        ->get();
                                        $maxDays = $daysInArrears->max('days_in_arrears');
                                        @endphp
                                        {{ $maxDays > 0 ? $maxDays . ' days in Arrears' : 'Up to date' }}
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
                                <h4 class="my-1">
                                    {{ number_format(\App\Models\LoanSchedule::where('customer_id', $customer->id)->sum('penalty_amount') - \App\Models\Repayment::where('customer_id', $customer->id)->sum('penalt_amount'), 2) }}
                                </h4>
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
                                    width="110" />
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
                                            <th scope="row">Category :</th>
                                            <td>{{ $customer->category }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Phone :</th>
                                            <td>{{ $customer->phone1 }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Gender :</th>
                                            <td>{{ $customer->sex }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Alt Phone :</th>
                                            <td>{{ $customer->phone2 ?? 'N/A'}}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Work :</th>
                                            <td>{{ $customer->work ?? 'N/A'}}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Work Address :</th>
                                            <td>{{ $customer->workAddress ?? 'N/A'}}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Id Type :</th>
                                            <td>{{ $customer->idType ?? 'N/A'}}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Id Number :</th>
                                            <td>{{ $customer->idNumber ?? 'N/A'}}</td>
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
                                            <th scope="row">Relation with business :</th>
                                            <td>{{ $customer->relation ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Company :</th>
                                            <td>{{ $customer->company->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Description :</th>
                                            <td>{{ $customer->company->description ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Registrar :</th>
                                            <td>{{ $customer->user->name ?? 'N/A' }}</td>
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
                            @can('edit customer')
                            <a href="{{ route('customers.edit', Hashids::encode($customer->id)) }}" class="btn btn-sm btn-warning flex-fill">
                                <i class="bx bx-edit"></i> Edit
                            </a>
                            @endcan
                            @can('delete customer')
                            <form action="{{ route('customers.destroy', Hashids::encode($customer->id)) }}" method="POST" class="flex-fill delete-form" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100" data-name="{{ $customer->name }}">
                                    <i class="bx bx-trash"></i> Delete
                                </button>
                            </form>
                            @endcan
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
                            <table class="table table-bordered dt-responsive nowrap table-striped" id="collateralTable">
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
                                        <td>{{ $collateral->type->name ?? 'N/A' }}</td> {{-- Access type name --}}
                                        <td>{{ number_format($collateral->amount, 2) }}</td> {{-- Assuming 'amount' field --}}
                                        <td>{{ $collateral->created_at->format('M d, Y') }}</td>
                                        <td class="text-center">
                                            @can('view cash collaterals')
                                            <a href="{{ route('cash_collaterals.show', Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-warning">
                                                View
                                            </a>
                                            @endcan

                                            @can('deposit cash collateral')
                                            <a href="{{ route('cash_collaterals.deposit',Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-primary">
                                                Deposit
                                            </a>
                                            @endcan

                                            @can('withdraw cash collateral')
                                            <a href="{{ route('cash_collaterals.withdraw', Hashids::encode($collateral->id)) }}" class="btn btn-sm btn-success">
                                                Withdraw
                                            </a>
                                            @endcan
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
                                <table class="table table-bordered dt-responsive nowrap table-striped" id="loansTable">
                                    <thead>
                                        <tr>
                                            <th>Loan ID</th>
                                            <th>Amount</th>
                                            <th>Total Amount</th>
                                            <th>Paid Amount</th>
                                            <th>Balance</th>
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
                                            <td>{{ number_format($loan->amount_total, 2) }}</td>
                                            <td>
                                                {{ number_format(\App\Models\Repayment::where('loan_id', $loan->id)->sum(\DB::raw('principal + interest')), 2) }}
                                            </td>
                                            <td>
                                                {{ number_format($loan->amount_total - \App\Models\Repayment::where('loan_id', $loan->id)->sum(\DB::raw('principal + interest')), 2) }}
                                            </td>
                                            <td>
                                                @if($loan->status === 'active')
                                                <span class="badge bg-success">{{ ucfirst($loan->status) }}</span>
                                                @elseif($loan->status === 'pending')
                                                <span class="badge bg-warning">{{ ucfirst($loan->status) }}</span>
                                                @elseif($loan->status === 'closed')
                                                <span class="badge bg-secondary">{{ ucfirst($loan->status) }}</span>
                                                @elseif($loan->status === 'defaulted')
                                                <span class="badge bg-danger">{{ ucfirst($loan->status) }}</span>
                                                @else
                                                <span class="badge bg-info">{{ ucfirst($loan->status) }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $loan->disbursed_on }}</td>
                                            <td class="text-center">
                                                @can('view loan details')
                                                <a href="{{ route('loans.show', Hashids::encode($loan->id)) }}" class="btn btn-sm btn-info">View</a>
                                                @endcan

                                                @can('edit loan')
                                                @if($loan->status == 'Pending')
                                                <a href="{{ route('loans.edit', Hashids::encode($loan->id)) }}" class="btn btn-sm btn-primary">Edit</a>
                                                @endif
                                                @endcan
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>

                <!---- document card --->

                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h5 class="font-size-16 text-truncate">Customer Documents</h5>
                            </div>

                            @if ($customer->filetypes->count())
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="fileTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>File Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($customer->filetypes as $index => $file)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $file->name }}</td>
                                            <td>
                                                @if($file->pivot->document_path)
                                                <a href="{{ asset('storage/' . $file->pivot->document_path) }}" class="btn btn-sm btn-info" target="_blank">View</a>
                                                <a href="{{ asset('storage/' . $file->pivot->document_path) }}" class="btn btn-sm btn-success" download>Download</a>
                                                @else
                                                <span class="text-danger">No file to view/download</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-center text-muted">No files uploaded.</p>
                            @endif
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