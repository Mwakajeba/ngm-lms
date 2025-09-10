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
            <!-- @can('create loan')
            <a href="#" class="btn btn-sm btn-primary">
                <i class="bx bx-plus"></i> Apply for Loan
            </a>
            @endcan -->
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
                                    src="{{ $customer->photo ? asset('storage/' . $customer->photo) : asset('assets/images/avatars/avatar-2.png') }}"
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
                                            <td>{{ $customer->description ?? 'N/A' }}</td>
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
                            
                            <!-- Send Message Button -->
                            <button type="button" class="btn btn-sm btn-info flex-fill" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                                <i class="bx bx-message"></i> Send Message
                            </button>
                            
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
                        <h5 class="card-title mb-4">Cash Deposits Records</h5>
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
                                                @php($encodedId = Hashids::encode($loan->id))
                                                <a href="{{ in_array($loan->status, ['applied','rejected']) ? route('loans.application.edit', $encodedId) : route('loans.edit', $encodedId) }}" class="btn btn-sm btn-primary">Edit</a>
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

        <!-- Send Message Modal -->
        <!-- Send SMS Modal -->
        <div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendMessageModalLabel">
                            <i class="bx bx-message me-2"></i>Send SMS to {{ $customer->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Important: action attribute points to the correct route -->
                    <form id="sendMessageForm" action="{{ route('customers.send-message', Hashids::encode($customer->id)) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $customer->phone1 }}" readonly>
                                <div class="form-text">Message will be sent to this number</div>
                            </div>

                            <div class="mb-3">
                                <label for="message_template" id="message_template" class="form-label">Message Template</label>
                                <select class="form-select" id="message_template" name="message_template">
                                    <option value="">Select a template...</option>
                                    <option value="payment_reminder">Payment Reminder</option>
                                    <option value="loan_approved">Loan Approved</option>
                                    <option value="loan_disbursed">Loan Disbursed</option>
                                    <option value="custom">Custom Message</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="message_content" class="form-label">Message Content</label>
                                <textarea class="form-control" id="message_content" name="message_content" rows="4" placeholder="Type your message here..." required></textarea>
                                <div class="form-text"><span id="character_count">0</span>/160 characters</div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Note:</strong> SMS charges may apply. Please ensure the message is appropriate and professional.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bx bx-x me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="sendMessageBtn">
                                <i class="bx bx-send me-1"></i>Send SMS Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- TEST BUTTON FOR LOADING STATE AND SWEETALERT -->

        <!-- TEST MODAL -->

        @endsection

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            // Check if we need to print a receipt after deposit
            @if(session('print_receipt') && session('receipt_data'))
                setTimeout(function() {
                    const receiptData = @json(session('receipt_data'));
                    printDepositReceipt(receiptData);
                }, 1000);
            @endif
        });

        function printDepositReceipt(receiptData) {
            // Create a new window for thermal printer (narrow width)
            const printWindow = window.open('', '_blank', 'width=320,height=600');
            
            // Set the document title
            const customerName = receiptData.customer_name.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_');
            const fileName = `Deposit_Receipt_${customerName}_${receiptData.date}`;
            
            const receiptHtml = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${fileName}</title>
                    <style>
                        @page {
                            size: 80mm 200mm;
                            margin: 0;
                            padding: 0;
                        }
                        
                        @media print {
                            body { 
                                font-family: 'Courier New', monospace; 
                                font-size: 10px; 
                                margin: 0; 
                                padding: 5px;
                                width: 280px;
                                max-width: 280px;
                                min-width: 280px;
                                page-break-after: avoid;
                                page-break-before: avoid;
                            }
                        }
                        
                        body { 
                            font-family: 'Courier New', monospace; 
                            font-size: 10px; 
                            margin: 0; 
                            padding: 5px;
                            width: 280px;
                            max-width: 280px;
                            min-width: 280px;
                        }
                        .header { text-align: center; margin-bottom: 8px; }
                        .title { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
                        .subtitle { font-size: 10px; margin-bottom: 8px; }
                        .divider { border-top: 1px dashed #000; margin: 8px 0; }
                        .row { display: flex; justify-content: space-between; margin: 2px 0; }
                        .label { font-weight: bold; }
                        .value { text-align: right; }
                        .total { font-weight: bold; font-size: 12px; }
                        .footer { text-align: center; margin-top: 15px; font-size: 8px; }
                        .center { text-align: center; }
                        .bold { font-weight: bold; }
                        .notes { margin: 8px 0; font-size: 9px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="title">SMARTFINANCE</div>
                        <div class="subtitle">Cash Deposit Receipt</div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="row">
                        <span class="label">Customer:</span>
                        <span class="value">${receiptData.customer_name}</span>
                    </div>
                    <div class="row">
                        <span class="label">Deposit Type:</span>
                        <span class="value">${receiptData.deposit_type}</span>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="row">
                        <span class="label">Receipt No:</span>
                        <span class="value">${receiptData.receipt_number}</span>
                    </div>
                    <div class="row">
                        <span class="label">Date:</span>
                        <span class="value">${receiptData.date}</span>
                    </div>
                    <div class="row">
                        <span class="label">Time:</span>
                        <span class="value">${receiptData.time}</span>
                    </div>
                    <div class="row">
                        <span class="label">Bank Account:</span>
                        <span class="value">${receiptData.bank_account}</span>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="row total">
                        <span class="label">Amount Deposited:</span>
                        <span class="value">TSHS ${parseFloat(receiptData.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="notes">
                        <div class="label">Notes:</div>
                        <div style="margin-top: 2px;">${receiptData.notes}</div>
                    </div>
                    
                    <div class="divider"></div>
                    
                    <div class="footer">
                        <div>Received by: ${receiptData.received_by}</div>
                        <div>Branch: ${receiptData.branch}</div>
                        <div style="margin-top: 5px;">Thank you for your deposit!</div>
                    </div>
                </body>
                </html>
            `;
            
            printWindow.document.write(receiptHtml);
            printWindow.document.close();
            
            // Auto print after a short delay
            setTimeout(() => {
                printWindow.print();
                // Auto close after printing (optional)
                setTimeout(() => {
                    printWindow.close();
                }, 2000);
            }, 500);
        }

        // Message template functionality
        document.getElementById('message_template').addEventListener('change', function() {
            const template = this.value;
            const messageContent = document.getElementById('message_content');
            const customerName = '{{ $customer->name }}';
            
            let templateText = '';
            
            switch(template) {
                case 'payment_reminder':
                    templateText = `Dear ${customerName}, this is a friendly reminder that your loan payment is due. Please make your payment to avoid any late fees. Thank you.`;
                    break;
                case 'loan_approved':
                    templateText = `Dear ${customerName}, congratulations! Your loan application has been approved. Please visit our office for the next steps. Thank you.`;
                    break;
                case 'loan_disbursed':
                    templateText = `Dear ${customerName}, your loan has been successfully disbursed. Please check your account. Thank you for choosing SmartFinance.`;
                    break;
                case 'custom':
                    templateText = '';
                    break;
                default:
                    templateText = '';
            }
            
            messageContent.value = templateText;
            updateCharacterCount();
        });

        // Character counter
        function updateCharacterCount() {
            var messageContent = document.getElementById('message_content');
            var characterCount = document.getElementById('character_count');
            if (messageContent && characterCount) {
                var count = messageContent.value.length;
                characterCount.textContent = count;
                if (count > 160) {
                    characterCount.style.color = 'red';
                } else if (count > 140) {
                    characterCount.style.color = 'orange';
                } else {
                    characterCount.style.color = 'green';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var messageContent = document.getElementById('message_content');
            if (messageContent) {
                messageContent.addEventListener('input', updateCharacterCount);
                updateCharacterCount();
            }
        });

        var sendMessageModal = document.getElementById('sendMessageModal');
        if (sendMessageModal) {
            sendMessageModal.addEventListener('shown.bs.modal', function () {
                updateCharacterCount();
            });
        }

        // Form submission
       
        document.addEventListener("DOMContentLoaded", function() {
        const messageTemplate = document.getElementById('message_template');
        const messageContent = document.getElementById('message_content');
        const characterCount = document.getElementById('character_count');

        // Define messages for templates
        const templateMessages = {
            payment_reminder: "Dear customer, this is a friendly reminder to pay your loan installment on time.",
            loan_approved: "Congratulations! Your loan has been approved. Please check your account for details.",
            loan_disbursed: "Your loan has been disbursed successfully. Thank you for choosing our services."
        };

        if (messageTemplate && messageContent && characterCount) {
            // When template changes, fill textarea
            messageTemplate.addEventListener('change', function() {
                const value = this.value;
                if (value === 'custom') {
                    messageContent.value = '';
                } else {
                    messageContent.value = templateMessages[value] || '';
                }
                characterCount.innerText = messageContent.value.length;
            });

            // Update character count as user types
            messageContent.addEventListener('input', function() {
                characterCount.innerText = this.value.length;
            });
        }
    
        const form = document.getElementById('sendMessageForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // stop normal submission

            const sendBtn = document.getElementById('sendMessageBtn');
            const originalText = sendBtn.innerHTML;
            const phoneNumber = document.getElementById('phone_number').value.trim();
            const messageContent = document.getElementById('message_content').value.trim();
            const modal = document.getElementById('sendMessageModal');
            const formElements = modal.querySelectorAll('input, textarea, select, button');
            const closeBtn = modal.querySelector('.btn-close');
            const modalBody = modal.querySelector('.modal-body');

            if (!phoneNumber || !messageContent) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields',
                    text: 'Please fill in phone number and message.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Confirm SMS Sending',
                html: `<p><strong>To:</strong> ${phoneNumber}</p>
                    <p><strong>Message:</strong></p>
                    <div class="border p-2 rounded bg-light" style="max-height:100px; overflow-y:auto;">
                    ${messageContent}</div>
                    <small class="text-muted">SMS charges may apply</small>`,
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
            // SMS Modal JS Cleanup
            function updateCharacterCount() {
                var messageContent = document.getElementById('message_content');
                var characterCount = document.getElementById('character_count');
                if (messageContent && characterCount) {
                    var count = messageContent.value.length;
                    characterCount.textContent = count;
                    if (count > 160) {
                        characterCount.style.color = 'red';
                    } else if (count > 140) {
                        characterCount.style.color = 'orange';
                    } else {
                        characterCount.style.color = 'green';
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                var messageContent = document.getElementById('message_content');
                var characterCount = document.getElementById('character_count');
                var form = document.getElementById('sendMessageForm');

                // Attach input event every time modal is shown
                var sendMessageModal = document.getElementById('sendMessageModal');
                if (sendMessageModal) {
                    sendMessageModal.addEventListener('shown.bs.modal', function () {
                        if (messageContent) {
                            messageContent.removeEventListener('input', updateCharacterCount);
                            messageContent.addEventListener('input', updateCharacterCount);
                            updateCharacterCount();
                        }
                    });
                }

                // Initial count on page load
                if (messageContent && characterCount) {
                    messageContent.addEventListener('input', updateCharacterCount);
                    updateCharacterCount();
                }

                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const sendBtn = document.getElementById('sendMessageBtn');
                        const originalText = sendBtn.innerHTML;
                        const phoneNumber = document.getElementById('phone_number').value.trim();
                        const msg = messageContent.value.trim();
                        const modal = document.getElementById('sendMessageModal');
                        const formElements = modal.querySelectorAll('input, textarea, select, button');
                        const closeBtn = modal.querySelector('.btn-close');
                        const modalBody = modal.querySelector('.modal-body');

                        if (!phoneNumber || !msg) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Required Fields',
                                text: 'Please fill in phone number and message.',
                                confirmButtonColor: '#3085d6'
                            });
                            return;
                        }

                        sendBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Sending...';
                        sendBtn.disabled = true;
                        formElements.forEach(el => el.disabled = true);
                        if (closeBtn) closeBtn.disabled = true;
                        modalBody.style.opacity = '0.7';

                        fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: data.success ? 'success' : 'error',
                                title: data.message || (data.success ? 'SMS sent successfully' : 'Failed to send SMS'),
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });
                            if (data.success) {
                                form.reset();
                                updateCharacterCount();
                                // Hide modal
                                const modalInstance = bootstrap.Modal.getInstance(modal);
                                modalInstance.hide();
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'Connection Error',
                                text: 'Could not send SMS. Check your internet connection.',
                                showConfirmButton: false,
                                timer: 2500,
                                timerProgressBar: true
                            });
                        })
                        .finally(() => {
                            sendBtn.innerHTML = originalText;
                            sendBtn.disabled = false;
                            formElements.forEach(el => el.disabled = false);
                            if (closeBtn) closeBtn.disabled = false;
                            modalBody.style.opacity = '1';
                        });
                    });
                }
            });
                        responseMsg = parsed.message || data.message || '';
                    } catch (e) {
                        responseMsg = data.response || data.message || '';
                    }
                } else if (typeof data.response === 'object' && data.response !== null) {
                    responseMsg = data.response.message || data.message || '';
                } else {
                    responseMsg = data.message || '';
                }
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Message Sent Successfully!',
                        html: `<div>SMS has been sent to <b>${document.getElementById('phone_number').value}</b><br><small>${responseMsg}</small></div>`,
                        confirmButtonColor: '#28a745',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: true
                    });
                    document.getElementById('message_template').value = '';
                    document.getElementById('message_content').value = '';
                    updateCharacterCount();
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('sendMessageModal'));
                    modalInstance.hide();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Send Message',
                        text: responseMsg || 'Unknown error occurred',
                        confirmButtonColor: '#dc3545',
                        footer: 'Please try again or contact support if the problem persists.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Failed to send message due to connection issues.',
                    confirmButtonColor: '#dc3545',
                    footer: 'Please check your internet connection and try again.'
                });
            })
            .finally(() => {
                // Reset button state and re-enable all form elements
                sendBtn.innerHTML = originalText;
                sendBtn.disabled = false;
                formElements.forEach(element => { element.disabled = false; });
                if (closeBtn) closeBtn.disabled = false;
                modalBody.style.opacity = '1';
            });
        }

        document.getElementById('testLoadingBtn').addEventListener('click', function() {
            const btn = this;
            const modal = document.getElementById('testLoadingModal');
            const closeBtn = modal.querySelector('.btn-close');
            btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Testing...';
            btn.disabled = true;
            if (closeBtn) closeBtn.disabled = true;
            const modalBody = modal.querySelector('.modal-body');
            modalBody.style.opacity = '0.7';
            setTimeout(function() {
                btn.innerHTML = 'Simulate Send';
                btn.disabled = false;
                if (closeBtn) closeBtn.disabled = false;
                modalBody.style.opacity = '1';
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Loading & SweetAlert2 Work!',
                    text: 'This is a test notification.',
                    confirmButtonColor: '#28a745',
                    timer: 2000,
                    timerProgressBar: true
                });
            }, 2000);
        });
        </script>
        @endpush